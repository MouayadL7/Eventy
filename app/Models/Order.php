<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

protected $fillable = [
    'client_id'
];

public function client() {
    return $this->belongsTo(client::class);
}

public function orderiteme()
{
    return $this->hasMany(Orderiteme::class);
}

public function Orderstate()
{
    return $this->hasMany(OrderState::class);
}


public function service()
{
    return $this->belongsToMany(service::class);
       
}

public function state()
{
    return $this->belongsToMany(state::class);

}
}