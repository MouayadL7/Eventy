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
        foreach ($orders as $key => $order)
        {
            $orders[$key] = $this->get_order($order, $lang);
        }
        return $orders;
    }

    public function get_order($order, $lang)
    {
        $order['num_of_bookings']  = $order->bookings->count();
        $order['total_price']      = $order->bookings->pluck('price')->sum();
        $order['order_state_name'] = (new OrderState)->get_order_state($order->order_state_id, $lang);

        return $order;
    }
}
