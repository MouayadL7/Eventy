<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $table = 'carts';
    protected $fillable = [
        'client_id'
    ];
    public function client() {
        return $this->belongsTo(Client::class);
    }
    public function items() {
        return $this->hasMany(Cart_item::class);
    }
}
