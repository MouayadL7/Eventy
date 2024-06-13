<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Booking;
class OrderController extends Controller
{

    /**
     * Confirm all items in the cart, making them bookings.
     */
    public function confirm()
    {
        $user = auth()->user()->userable;

        $cart = $user->cart;
        $cartItems = $cart->items;

        $order = Order::create([
            'client_id' => $user->id,
            'state' => 'pending',
        ]);

        foreach ($cartItems as $item) {
            if (Booking::where('service_id', $item->service_id)->where('event_date', $item->event_date)->exists()) {
                return response()->json(['message' => 'Some items are already booked.'], 400);
            }

            Booking::create([
                'client_id' => $user->id,
                'service_id' => $item->service_id,
                'event_date' => $item->event_date,
                'order_id' => $order->id,
            ]);
            $item->delete();
        }

        return response()->json(['message' => 'Cart confirmed and items booked.', 'order' => $order]);
    }

    /**
     * Cancel the specified order.
     */
    public function cancelOrder(Order $order)
    {

        if ($order->client_id !== auth()->user()->userable->id) {
            return response()->json([
                'message' => 'you cannot delete this order because it is not belong to you'
            ]);
        }
        if ($order->state !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be canceled.'], 400);
        }
        $order->delete();


        return response()->json(['message' => 'Order canceled.']);
    }


}
