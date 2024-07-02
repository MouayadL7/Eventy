<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Order;
use App\Models\OrderState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderStateController extends BaseController
{


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        //
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
    public function updateOrderState(Request $request, $id)
    {
        $order = Order::find($id);
        $validator = Validator::make($request->all(), [
            'state' => 'required|in:Pending,In Preparation,Done'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $order_state = OrderState::query()->where('name_EN', $request->state)->first();
        $order->update(['order_state_id' => $order_state->id]);

        return $this->sendResponse($order);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
