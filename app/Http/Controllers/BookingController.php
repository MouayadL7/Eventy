<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Booking;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->has('order_id'))
        {
            $order = Order::with('bookings.service')->find($request->order_id);
            if (is_null($order)) {
                return $this->sendError(['message' => 'There is not order with this ID']);
            }
            
            return $this->sendResponse($order);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $booking = Booking::find($id);
        if (is_null($booking)) {
            return $this->sendError(['message' => 'There is not booking with this ID']);
        }

        $booking->delete();
        return $this->sendResponse();
    }

    public function get_booked_dates(Request $request)
   {}
}
