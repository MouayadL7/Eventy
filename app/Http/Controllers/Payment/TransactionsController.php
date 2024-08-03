<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Transactions;
use App\Models\TransactionStatuses;
use App\Models\TransactionTypes;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionsController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $transactions = (new Transactions)->getTransactionsForUserInMonth($request, request('lang'));

        $totalRecieveCash = (new Transactions)->getRecieveCash($transactions);
        $totalPayCash     = (new Transactions)->getPayCash($transactions);

        $transactions = (new Transactions)->get_all_transactions($transactions, request('lang'));

        $data = [
            'transaction' => $transactions,
            'total_recieve_cash' => $totalRecieveCash,
            'total_pay_cash' => $totalPayCash
        ];

        return $this->sendResponse($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $storeTransactionRequest = new StoreTransactionRequest();
            $validator = Validator::make($request->all(), $storeTransactionRequest->rules());

            if ($validator->fails()) {
                return $this->sendError($validator->errors());
            }

            $transaction = Transactions::create($request->all());

            DB::commit();
            return $this->sendResponse($transaction);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->sendError(['message' => $ex->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $lang = \request('lang');
        $transaction = Transactions::find($id);
        $transaction = $transaction->get_info($transaction, $lang);

        return $this->sendResponse($transaction);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transactions $transactions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Transactions $transaction, $lang)
    {
        $transaction_status = TransactionStatuses::query()->when($lang == 'en',
            function($query) {
                return $query->select('id')->where('name_EN', 'cancel');
            }
            ,
            function($query) {
                return $query->select('id')->where('name_AR', 'ملغي');
            }
        );

        $transaction->update([
            'transaction_status_id' => $transaction_status->id
        ]);

        return $this->sendResponse();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transactions $transactions)
    {
        //
    }
}
