<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderState extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id', 'state_id'
    ];

    public function order()
    {
        return $this->belongsTo(order::class);
    }
    public function state()
    {
        return $this->belongsTo(state::class);
    }
}
