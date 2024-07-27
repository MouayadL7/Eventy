<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderState extends Model
{
    use HasFactory;

    const OrderState_Pending = 1;
    const OrderState_In_Preparation = 2;
    const OrderState_Done = 3;

    protected $fillable = ['order_id', 'state'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function get_order_state($id , string $lang){
        $order_state = OrderState::query()->when($lang == 'en' ,
            function($query) use($id){
                return $query->select('name_EN as name')->find($id);
            }
            ,
            function($query) use($id){
                return $query->select('name_AR as name')->find($id);
            }
        );
        return $order_state->name;
    }
}
