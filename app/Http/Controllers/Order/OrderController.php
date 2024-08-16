<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Payment\BudgetController;
use App\Models\Order;
use App\Models\Booking;
use App\Models\Budget;
use App\Models\Categoury;
use App\Models\OrderState;
use App\Models\Service;
use App\Models\Transactions;
use App\Models\TransactionStatuses;
use App\Models\TransactionTypes;
use App\Models\User;
use App\Notifications\UserNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OrderController extends BaseController
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (Gate::allows('isClient', $user)) {
            return $this->indexByClientId();
        }
        else if (Gate::allows('isSponsor', $user)) {
            return $this->indexBySponsorId($request);
        }

        if ($request->has('event_date')) {
            $orders = Order::with('bookings.service.images')
                            ->whereRaw('id IN (SELECT order_id FROM bookings WHERE bookings.event_date = ?)', [$request->event_date])
                            ->get();
        }
        else {
            $orders = Order::query()->with('bookings.service.images')->get();
        }

        return $this->sendResponse((new Order)->get_orders($orders, request('lang')));
    }

    public function indexByClientId()
    {
        $orders = Order::query()->with('bookings.service.images')
            ->where('client_id', auth()->user()->userable_id)
            ->where('order_state_id', '<>', OrderState::ORDERSTATE_DONE)
            ->where('order_state_id', '<>',OrderState::ORDERSTATE_CANCELED)
            ->get();
        return $this->sendResponse((new Order)->get_orders($orders, request('lang')));
    }

    public function indexBySponsorId(Request $request)
    {
        $sponsor = auth()->user()->userable;
        if ($request->has('event_date')) {
            $orders = Order::query()
                        ->with('bookings.service.images')
                        ->whereRaw('id IN (SELECT order_id FROM bookings WHERE service_id = ? AND bookings.event_date = ?)', [$sponsor->service_id, $request->event_date])
                        ->where('order_state_id', '<>', OrderState::ORDERSTATE_CANCELED)
                        ->get();
        }
        else {
            $orders = Order::query()
                        ->with('bookings.service.images')
                        ->whereRaw('id IN (SELECT order_id FROM bookings WHERE service_id = ?)', [$sponsor->service_id])
                        ->where('order_state_id', '<>', OrderState::ORDERSTATE_DONE)
                        ->where('order_state_id', '<>', OrderState::ORDERSTATE_CANCELED)
                        ->get();
        }

        return $this->sendResponse((new Order)->get_orders($orders, request('lang')));
    }

    public function show($id)
    {
        $order = Order::with('bookings.service.images')->find($id);
        if (is_null($order)) {
            return $this->sendError('There is no order with this ID');
        }

        return $this->sendError($order);
    }

    /**
     * Confirm all items in the cart, making them bookings.
     */
    public function confirm()
    {
        DB::beginTransaction();
        try {
            $user = auth()->user()->userable;
            $cart = $user->cart;
            $cartItems = $cart->cart_items()->with('service.sponsor.user')->get();

            $order = Order::create([
                'client_id' => $user->id,
                'order_state_id' => 1
            ]);

            $sponsor_price = 0;
            $total_price = 0;
            $sponsor = null;
            foreach ($cartItems as $item) {
                Booking::create([
                    'service_id' => $item->service_id,
                    'event_date' => $item->event_date,
                    'order_id' => $order->id,
                    'price' => $item->service->price
                ]);
                if ($item->service->categoury_id != Categoury::CATEGOURY_ORGANIZER) {
                    $total_price += $item->service->price;
                }
                else {
                    $sponsor_price = $item->service->price;
                    $sponsor = $item->service->sponsor->user;
                }
                $item->delete();
            }

            if (is_null($sponsor)) {
                DB::rollBack();
                return $this->sendError('You must add sponsor to your order');
            }

            $request = new Request();
            $request['balance'] = $total_price;
            $request['sponsor_price'] = $sponsor_price;
            (new BudgetController)->pay($request);

            // To notify the user
            $user_name = $user->first_name . ' ' . $user->last_name;
            $sponsor->notify(new UserNotification('You Have a New Order!', 'A new order has been placed by ' . $user_name . '. Click here to view the order and start preparing.', ['order_id' => $order->id]));

            DB::commit();

            return $this->sendResponse($order);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     * Cancel the specified order.
     */
    public function cancelOrder($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::find($id);
            if (is_null($order)) {
                return $this->sendError('There is no order with this ID');
            }
            if ($order->client_id != auth()->user()->userable_id) {
                return $this->sendError(['you cannot delete this order because it is not belong to you']);
            }

            $total_price = $order->bookings()->pluck('price')->sum();
            $sponsor_price = 0;
            $bookings = $order->bookings()->with('service.sponsor.user')->get();
            foreach ($bookings as $booking)
            {
                if ($booking->service->categoury_id == Categoury::CATEGOURY_ORGANIZER) {
                    $total_price -= $booking->price;
                    $sponsor_price = $booking->price;
                    break;
                }
            }

            if ($order->order_state_id == OrderState::ORDERSTATE_IN_PREPARATION) {
                $daysSinceUpdated = $order->updated_at->diffInDays(Carbon::now());
                if ($daysSinceUpdated >= 4) {
                    return $this->sendError('You cannot delete this order because it has been 4 days since it was ordered.');
                }
                else {
                    $total_price = $total_price - ($total_price * (25 * $daysSinceUpdated) / 100);
                    $sponsor_get = ($sponsor_price * (25 * $daysSinceUpdated) / 100);
                    $sponsor_price = $sponsor_price - $sponsor_get;
                    if ($sponsor_get > 0) {
                        (new BudgetController)->charge(new Request([
                            'balance' => $sponsor_price,
                            'user_id' => Auth::id()
                        ]));
                    }
                }
            }

            (new BudgetController)->cancel(new Request([
                'balance' => $total_price,
                'sponsor_price' => $sponsor_price
            ]));

            (new OrderStateController)->updateOrderState(new Request(['state' => 'Canceled']), $id);

            DB::commit();
            return $this->sendResponse();
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->sendError($ex->getMessage());
        }
    }
}
