<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionStatuses extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_EN', 'name_AR'
    ];

    public function transactions() : HasMany
    {
        return $this->hasMany(Transactions::class);
    }

    public function get_transaction_status($id , string $lang){
        $transaction_status = TransactionStatuses::query()->when($lang == 'en' ,
            function($query) use($id){
                return $query->select('name_EN as name')->find($id);
            }
            ,
            function($query) use($id){
                return $query->select('name_AR as name')->find($id);
            }
        );
        $transaction_status->name;
    }
}
