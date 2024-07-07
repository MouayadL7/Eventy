<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Payment\BudgetController;
use App\Models\Order;
use App\Models\Booking;
use App\Models\Budget;
use App\Models\Categoury;
use App\Models\Service;
use App\Models\Transactions;
use App\Models\TransactionStatuses;
use App\Models\TransactionTypes;
use App\Models\User;
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
        $orders = Order::query()->with('bookings.service.images')->where('client_id', auth()->user()->userable_id)->get();
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
                        ->get();
        }

        return $this->sendResponse((new Order)->get_orders($orders, request('lang')));
    }

    /**
     * Confirm all items in the cart, making them bookings.
     */
    public function confirm()
    {
        $user = auth()->user()->userable;
        $cart = $user->cart;
        $cartItems = $cart->cart_items()->with('service.sponsor.user')->get();

        $order = Order::create([
            'client_id' => $user->id,
            'order_state_id' => 1
        ]);

        $total_price = 0;
        $sponsor_price = 0;
        foreach ($cartItems as $item) {
            Booking::create([
                'service_id' => $item->service_id,
                'event_date' => $item->event_date,
                'order_id' => $order->id,
                'price' => $item->service->price
            ]);
            if ($item->service->categoury_id == Categoury::CATEGOURY_ORGANIZER) {
                $budget = Budget::query()->where('user_id', $item->service->sponsor->user->id)->first();
                $budget->update([
                    'balance' => $budget->balance + $item->service->price
                ]);
                $transaction_status = TransactionStatuses::query()->where('name_EN', 'complete')->first();
                $transaction_type = TransactionTypes::query()->where('name_EN', 'recieve Cash')->first();
                Transactions::create([
                    'user_id'  => $item->service->sponsor->user->id,
                    'transaction_status_id' => $transaction_status->id,
                    'transaction_type_id' => $transaction_type->id,
                    'balance' => $item->service->price
                ]);
                $sponsor_price = $item->service->price;
            }
            else {
                $total_price += $item->service->price;
            }
            $item->delete();
        }

        $request = new Request();
        $request['balance'] = $total_price;
        $request['sponsor_price'] = $sponsor_price;
        $response = (new BudgetController)->pay($request);

        return $this->sendResponse($order);
    }

    /**
     * Cancel the specified order.
     */
    public function cancelOrder($id)
    {
        $order = Order::find($id);
        if ($order->client_id != auth()->user()->userable_id) {
            return $this->sendError(['you cannot delete this order because it is not belong to you']);
        }
        if ($order->order_state_id != 1) {
            return $this->sendError(['message' => 'Only pending orders can be canceled.']);
        }

        $total_price = $order->bookings()->pluck('price')->sum();
        $sponsor_price = 0;
        $bookings = $order->bookings()->with('service.sponsor.user')->get();
        foreach ($bookings as $booking)
        {
            if ($booking->service->categoury_id == Categoury::CATEGOURY_ORGANIZER) {
                $total_price -= $booking->price;
                $budget = Budget::query()->where('user_id', $booking->service->sponsor->user->id)->first();
                $budget->update([
                    'balance' => $budget->balance - $booking->price
                ]);
                $transaction_status = TransactionStatuses::query()->where('name_EN', 'cancel')->first();
                $transaction_type = TransactionTypes::query()->where('name_EN', 'recieve Cash')->first();
                Transactions::create([
                    'user_id'  => $booking->service->sponsor->user->id,
                    'transaction_status_id' => $transaction_status->id,
                    'transaction_type_id' => $transaction_type->id,
                    'balance' => $booking->price
                ]);
                $sponsor_price = $booking->price;
            }
        }
        (new BudgetController)->cancel(new Request([
            'balance' => $total_price,
            'sponsor_price' => $sponsor_price
        ]));

        $order->delete();
        return $this->sendResponse();
    }
}
