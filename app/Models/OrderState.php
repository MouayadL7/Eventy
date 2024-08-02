<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderState extends Model
{
    use HasFactory;

    const ORDERSTATE_PENDING = 1;
    const ORDERSTATE_IN_PREPARATION = 2;
    const ORDERSTATE_DONE = 3;
    const ORDERSTATE_CANCELED = 4;

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
