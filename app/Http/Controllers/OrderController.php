<?php

namespace App\Http\Controllers;

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
            ->where('order_state_id', '<>', OrderState::OrderState_Done)
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
                        ->get();
        }
        else {
            $orders = Order::query()
                        ->with('bookings.service.images')
                        ->whereRaw('id IN (SELECT order_id FROM bookings WHERE service_id = ?)', [$sponsor->service_id])
                        ->where('order_state_id', '<>', OrderState::OrderState_Done)
                        ->get();
        }

        return $this->sendResponse((new Order)->get_orders($orders, request('lang')));
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
                }
                $item->delete();
            }

            $request = new Request();
            $request['balance'] = $total_price;
            $request['sponsor_price'] = $sponsor_price;
            $response = (new BudgetController)->pay($request);

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

            if ($order->order_state_id == OrderState::OrderState_In_Preparation) {
                $daysSinceUpdated = $order->updated_at->diffInDays(Carbon::now());
                if ($daysSinceUpdated >= 4) {
                    return $this->sendError('You cannot delete this order because it has been 4 days since it was ordered.');
                }
                else {
                    $total_price = $total_price - ($total_price * (25 * $daysSinceUpdated) / 100);
                    $sponsor_price = $sponsor_price - ($sponsor_price * (25 * $daysSinceUpdated) / 100);
                }
            }

            (new BudgetController)->cancel(new Request([
                'balance' => $total_price,
                'sponsor_price' => $sponsor_price
            ]));

            $order->delete();
            DB::commit();
            return $this->sendResponse();
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->sendError($ex->getMessage());
        }
    }
}
