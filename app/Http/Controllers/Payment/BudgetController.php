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
            'booking_id' => ['required', 'exists:bookings,id']
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $lang = \request('lang');
        $transaction_status = TransactionStatuses::query()->when($lang == 'en',
            function($query) {
                return $query->select('id')->where('name_EN', 'complete');
            }
            ,
            function($query) {
                return $query->select('id')->where('name_AR', 'مكتمل');
            }
        )->first();


        $transaction_type = TransactionTypes::query()->when($lang == 'en',
            function($query) {
                return $query->select('id')->where('name_EN', 'recieve Cash');
            }
            ,
            function($query) {
                return $query->select('id')->where('name_AR', 'تلقي نقداً');
            }
        )->first();

        $transaction = Transactions::create([
            'book_id' => $request->booking_id,
            'user_id'  => Auth::id(),
            'transaction _status_id' => $transaction_status->id,
            'transaction_type_id' => $transaction_type->id,
            'balance' => $request->balance,
        ]);

        $budget = Budget::where('user_id', Auth::id())->first();
        $budget->update(['balance' => $budget->balance + $request->balance]);

        return $this->sendResponse($budget);
    }


    public function pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'balance' => ['required', 'numeric','min:1'],
            'booking_id' => ['required', 'exists:bookings,id']
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $lang = \request('lang');
        $transaction_status = TransactionStatuses::query()->when($lang == 'en',
            function($query) {
                return $query->select('id')->where('name_EN', 'complete');
            }
            ,
            function($query) {
                return $query->select('id')->where('name_AR', 'مكتمل');
            }
        )->first();

        $transaction_type = TransactionTypes::query()->when($lang == 'en',
            function($query) {
                return $query->select('id')->where('name_EN', 'pay Cash');
            }
            ,
            function($query) {
                return $query->select('id')->where('name_AR', 'دفع نقداً');
            }
        )->first();

        $transaction = Transactions::create([
            'book_id' => $request->booking_id,
            'user_id'  => Auth::id(),
            'transaction _status_id' => $transaction_status->id,
            'transaction_type_id' => $transaction_type->id,
            'balance' => $request->balance,
        ]);

        $budget = Budget::where('user_id', Auth::id())->first();
        $budget->update(['balance' => $budget->balance - $request->balance]);

        return $this->sendResponse($budget);
    }

    public function get_budget()
    {
        $budget = Budget::where('user_id', Auth::id())->first();
        return $this->sendResponse($budget);
    }
}
