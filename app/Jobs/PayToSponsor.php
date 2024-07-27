<?php

namespace App\Jobs;

use App\Http\Controllers\Payment\BudgetController;
use App\Models\Order;
use App\Models\OrderState;
use App\Models\Sponsor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PayToSponsor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;
    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::find($this->data['order_id']);
        if (!is_null($order) && $order->order_state_id != OrderState::OrderState_Done) {
            (new BudgetController)->charge(new Request([
                'balance' => Sponsor::find($this->data['sponsor_id'])->service->price,
                'user_id' => $this->data['sponsor_id']
            ]));
        }
    }
}
