<?php

namespace App\Http\Controllers;


use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class OrderStateController extends Controller
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
        if (!$this->isSponsor()) {
            return response()->json(['message' => 'Only sponsors can update order state.'], 403);
        }

        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'state' => 'required|in:pending,in_preparation,done',
        ]);

        $order->update(['state' => $validated['state']]);

        return response()->json(['message' => 'Order state updated.', 'order' => $order]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
