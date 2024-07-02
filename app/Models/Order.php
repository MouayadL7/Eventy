<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['client_id', 'order_state_id'];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
    public function states(): HasMany
    {
        return $this->hasMany(OrderState::class);
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function get_orders($orders, $lang)
    {
        foreach ($orders as $order)
        {
            $order['order_state_name'] = (new OrderState)->get_order_state($order->order_state_id, $lang);
        }
        return $orders;
    }
}
