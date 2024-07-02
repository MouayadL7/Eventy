<?php

namespace App\Jobs;

use App\Models\Cart_item;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveUnconfirmedCartItems implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $cart_item;
    public function __construct(Cart_item $cart_item)
    {
        $this->cart_item = $cart_item;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cart_item = Cart_item::find($this->cart_item->id);
        if ($cart_item) {
            $cart_item->delete();
        }
    }
}
