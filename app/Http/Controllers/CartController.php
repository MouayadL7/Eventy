<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Cart_item;
use App\Models\Booking;
use App\Models\Service;
use App\Jobs\RemoveUnconfirmedCartItems;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class CartController extends Controller
{
   /**
    * Display a listing of the cart items.
    */
   public function index()
   {
        $client = auth()->user()->userable;
        $cart = $client->cart;
        return response()->json(['cart_items' => $cart->items]);
    }

   /**
    * Store a newly created cart item in storage.
    */
   public function store(Request $request){

   $client = auth()->user()->userable;

   $validated = $request->validate([
       'service_id' => 'required|exists:services,id',
       'event_date' => 'required|date|after:today',

   ]);

   $service = Service::find($validated['service_id']);
   $eventDate = $validated['event_date'];
   $data = $service->cartItems()->where('event_date', $request->event_date)->first();
   if ($data) {
        return response()->json(['message' => 'This date is already booked for this service.'], 400);
   }


   $cart = $client->cart;

   $cartItem = $cart->items()->create([
       'service_id' => $service->id,
       'event_date' => $eventDate,
   ]);

   // Dispatch job to remove the cart item after 6 hours
   RemoveUnconfirmedCartItems::dispatch($cartItem)->delay(now()->addHours(6));

   return response()->json(['message' => 'Service added to cart.', 'cart_item' => $cartItem]);
   }

   /**
    * Display the specified cart item.
    */
   public function show(Cart $cart)
   {
    return $cart->items;

}
   /**
    * Remove the specified cart item from storage.
    */
   public function destroy($id)
   {
    $cartItem = Cart_item::where('id', $id)
        ->whereHas('cart', function ($query) {
            $query->where('client_id', auth()->user()->userable);
        })
        ->firstOrFail();

    $cartItem->delete();

    return response()->json(['message' => 'Cart item deleted.']);
}

   /**
    * Get all booked dates for a selected service.
    */
   public function getBookedDates($serviceId)
   {
    $service = Service::findOrFail($serviceId);
    $bookedDates = Booking::where('service_id', $serviceId)->pluck('event_date');
    return response()->json(['booked_dates' => $bookedDates]);
}

}
