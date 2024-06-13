<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';
    protected $fillable = ['client_id', 'state'];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
    public function states(): HasMany
    {
        return $this->hasMany(OrderState::class);
    }

}
