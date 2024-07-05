<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Cart;
use App\Models\Cart_item;
use App\Models\Booking;
use App\Models\Service;
use App\Jobs\RemoveUnconfirmedCartItems;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isEmpty;

class CartController extends BaseController
{
    /**
    * Display a listing of the cart items.
    */
    public function index()
    {
        $user = auth()->user();
        $cart = $user->userable->cart;

        $cart_items = Cart_item::query()
                            ->with('service.images')
                            ->where('cart_id', $cart->id)
                            ->select('id', 'event_date', 'service_id')
                            ->get();
        if ($cart_items->isEmpty()) {
            return $this->sendResponse();
        }

        $sum = DB::table('cart_items')
                ->join('services', 'cart_items.service_id', '=', 'services.id')
                ->where('cart_items.cart_id', $cart->id)
                ->sum('services.price');

        $data = [
            'cart_items' =>$cart_items,
            'total_sum'  =>$sum,
            'confirm'    =>$user->budget->balance >= $sum,
        ];

        return $this->sendResponse($data);
    }

    /**
    * Store a newly created cart item in storage.
    */
    public function store(Request $request)
    {
        $client = auth()->user()->userable;
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'event_date' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $service = Service::find($request->service_id);
        $eventDate = $request['event_date'];
        $data = $service->cart_items->isEmpty() ? null : $service->cart_items()->where('event_date', $eventDate)->first();
        $data2 = $service->bookings->isEmpty() ? null : $service->bookings()->where('event_date', $eventDate)->first();
        if ($data || $data2) {
            return $this->sendError(['message' => 'This date is already booked for this service.']);
        }

        $cart = $client->cart;
        $cart_item = Cart_item::query()->create([
            'service_id' => $service->id,
            'cart_id'    => $cart->id,
            'event_date' => $eventDate
        ]);


        // Dispatch job to remove the cart item after 6 hours
        RemoveUnconfirmedCartItems::dispatch($cart_item)->delay(now()->addHours(6));

        return $this->sendResponse($cart_item);
    }

    /**
    * Display the specified cart item.
    */
    public function show($id)
    {
        //
    }

    /**
    * Remove the specified cart item from storage.
    */
    public function destroy($id)
    {
        $cartItem = Cart_item::find($id);
        if (is_null($cartItem)) {
            return $this->sendError(['There is no cart_item with this ID']);
        }

        $cartItem->delete();
        return $this->sendResponse();
    }

    /**
    * Get all booked dates for a selected service.
    */
    public function getBookedDates($serviceId)
    {
        $service = Service::find($serviceId);
        if (is_null($service)) {
            return $this->sendError(['message' => 'There is no service with this ID']);
        }
        $bookedDates  = Cart_item::query()->where('service_id', $service->id)->pluck('event_date');
        $bookedDates2 = Booking::query()->where('service_id', $service->id)->pluck('event_date');
        return $this->sendResponse($bookedDates->merge($bookedDates2));
    }
}
