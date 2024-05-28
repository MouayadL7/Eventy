<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Transactions;
use App\Models\TransactionStatuses;
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
        $lang = \request('lang');
        if ($request->has('user_id')) {
            return $this->indexByUserId($request->user_id, $lang);
        }

        $transactions = Transactions::all();
        $transactions = (new Transactions)->get_all_transactions($transactions, $lang);

        return $this->sendResponse($transactions);
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

    public function indexByUserId(string $userId, $lang)
    {
        if (is_null(User::find($userId))) {
            return $this->sendError(['message' => 'There is not user with this ID']);
        }

        $transactions = Transactions::where('user_id', $userId)->get();
        $transactions = (new Transactions)->get_all_transactions($transactions, $lang);

        return $this->sendResponse($transactions);

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
