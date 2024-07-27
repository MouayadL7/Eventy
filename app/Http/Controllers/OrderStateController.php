<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Payment\BudgetController;
use App\Jobs\PayToSponsor;
use App\Models\Booking;
use App\Models\Order;
use App\Models\OrderState;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'state' => 'required|in:In Preparation,Done'
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors());
            }

            $order = Order::find($id);
            if (is_null($order)) {
                return $this->sendError('There is no order with this ID');
            }

            $order_state = OrderState::query()->where('name_EN', $request->state)->first();
            if ($order->order_state_id == $order_state->id) {
                return $this->sendError('The order is state already ' . $request->state);
            }
            else if ($order->order_state_id == OrderState::OrderState_Done) {
                return $this->sendError('You cannot update state of the order because it is Done');
            }
            if ($order_state->id == OrderState::OrderState_In_Preparation) {
                dispatch(new PayToSponsor([
                    'order_id' => $order->id,
                    'sponsor_id' => Auth::id()
                ]))->delay(now()->addDays(4));
            }
            else {
                $daysSinceUpdated = $order->updated_at->diffInDays(Carbon::now());
                if ($daysSinceUpdated < 4) {
                    (new BudgetController)->charge(new Request([
                        'balance' => auth()->user()->userable->service->price,
                        'user_id' => Auth::id()
                    ]));
                }
            }

            $order->update(['order_state_id' => $order_state->id]);
            DB::commit();
            return $this->sendResponse($order);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
