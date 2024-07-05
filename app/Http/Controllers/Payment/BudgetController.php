<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StoreBudgetRequest;
use App\Models\Budget;
use App\Models\Transactions;
use App\Models\TransactionStatuses;
use App\Models\TransactionTypes;
use App\Models\User;
use Exception;
use Google\Service\Spanner\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\MockObject\ReturnValueNotConfiguredException;

class BudgetController extends BaseController
{
    public function charge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'balance' => ['required', 'numeric','min:1'],
            'user_id' => ['required', 'exists:users,id']
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $transaction_status = TransactionStatuses::query()->where('name_EN', 'complete')->first();
        $transaction_type = TransactionTypes::query()->where('name_EN', 'recieve Cash')->first();
        Transactions::create([
            'user_id'  => $request->user_id,
            'transaction_status_id' => $transaction_status->id,
            'transaction_type_id' => $transaction_type->id,
            'balance' => $request->balance,
        ]);

        $budget = Budget::where('user_id', $request->user_id)->first();
        $budget->update(['balance' => $budget->balance + $request->balance]);

        return $this->sendResponse($budget);
    }

    public function pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'balance' => ['required', 'numeric','min:0'],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $transaction_status = TransactionStatuses::query()->where('name_EN', 'complete')->first();
        $transaction_type = TransactionTypes::query()->where('name_EN', 'pay Cash')->first();
        Transactions::create([
            'user_id'  => Auth::id(),
            'transaction_status_id' => $transaction_status->id,
            'transaction_type_id' => $transaction_type->id,
            'balance' => $request->balance + $request->sponsor_price,
        ]);

        $budget = Budget::where('user_id', Auth::id())->first();
        $budget->update(['balance' => $budget->balance - $request->balance - $request->sponsor_price]);

        $admin_budget = Budget::where('user_id', 1)->first();
        $admin_budget->update(['balance' => $admin_budget->balance + $request->balance]);

        return $this->sendResponse($budget);
    }

    public function cancel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'balance' => ['required', 'numeric','min:0'],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $transaction_status = TransactionStatuses::query()->where('name_EN', 'cancel')->first();
        $transaction_type = TransactionTypes::query()->where('name_EN', 'recieve Cash')->first();
        Transactions::create([
            'user_id'  => Auth::id(),
            'transaction_status_id' => $transaction_status->id,
            'transaction_type_id' => $transaction_type->id,
            'balance' => $request->balance + $request->sponsor_price,
        ]);

        $budget = Budget::where('user_id', Auth::id())->first();
        $budget->update(['balance' => $budget->balance + $request->balance + $request->sponsor_price]);

        $admin_budget = Budget::where('user_id', 1)->first();
        $admin_budget->update(['balance' => $admin_budget->balance - $request->balance]);

        return $this->sendResponse($budget);
    }

    public function get_budget()
    {
        $budget = Budget::where('user_id', Auth::id())->select('balance')->first();
        return $this->sendResponse($budget);
    }
}
