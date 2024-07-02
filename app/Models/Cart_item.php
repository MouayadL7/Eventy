<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart_item extends Model
{
    use HasFactory;
    protected $table = "cart_items";

    protected $fillable = [
        'cart_id',
        'service_id',
        'event_date'
    ];

    protected $dates = [
        'event_date'
    ];

    public function cart() : BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function service() : BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
