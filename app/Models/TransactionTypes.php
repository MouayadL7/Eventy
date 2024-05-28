<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionTypes extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_EN', 'name_AR'
    ];

    public function transactions() : HasMany
    {
        return $this->hasMany(Transactions::class);
    }

    public function get_transaction_type($id , string $lang ){
        $transaction_type = TransactionTypes::query()->when($lang == 'en' ,
            function($query) use($id){
                return $query->select('name_EN as name')->find($id);
            }
            ,
            function($query) use($id){
                return $query->select('name_AR as name')->find($id);
            }
        );
        $transaction_type->name;
    }
}
