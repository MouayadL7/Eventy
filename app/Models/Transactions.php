<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transactions extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction_type() : BelongsTo
    {
        return $this->belongsTo(TransactionTypes::class);
    }

    public function transaction_status() : BelongsTo
    {
        return $this->belongsTo(TransactionStatuses::class);
    }

    public function get_all_transactions($transactions, $lang)
    {
        foreach ($transactions as $key => $transaction) {
            $transactions[$key] = $this->get_info($transaction, $lang);
        }
        return $transactions;
    }

    public function get_info($transaction, $lang)
    {
        return [
            'id' => $transaction->id,
            'balance' => $transaction->balance,
            'code' => is_null($transaction->code) ? "" : $transaction->code,
            'transaction_type_name' => (new TransactionTypes)->get_transaction_type($transaction->transaction_type_id, $lang, 0),
            'transaction_status_name' => (new TransactionStatuses)->get_transaction_status($transaction->transaction_status_id, $lang, 0),
            'user_id' => $transaction->user_id,
        ];
    }

    public function getTransactionsForUserInMonth($request, $lang)
    {
        $transactions = self::where('user_id', $request->userId)
                            ->whereYear('created_at', $request->year)
                            ->whereMonth('created_at', $request->month)
                            ->get();

        return $transactions;
    }

    public function getRecieveCash($transactions)
    {
        $transaction_type = TransactionTypes::query()->where('name_EN', 'recieve Cash')->select('id')->first();
        return $transactions->where('transaction_type_id', $transaction_type->id)->sum('balance');
    }

    public function getPayCash($transactions)
    {
        $transaction_type = TransactionTypes::query()->where('name_EN', 'pay Cash')->select('id')->first();
        return $transactions->where('transaction_type_id', $transaction_type->id)->sum('balance');
    }
}
