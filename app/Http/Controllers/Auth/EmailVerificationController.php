<?php

namespace App\Http\Controllers\Auth;

use App\Events\EmailVerification as EventsEmailVerification;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Http\Requests\EmailVerificationRequest;
use App\Models\EmailVerification;
use App\Models\User;

use Illuminate\Http\JsonResponse;


use Illuminate\Support\Facades\Validator;

class EmailVerificationController extends BaseController
{
    public function userCheckCode(Request $request) : JsonResponse
    {
        $emailVerificationRequest = new EmailVerificationRequest();
        $validator = Validator::make($request->all(), $emailVerificationRequest->rules());

        if($validator->fails())
        {
            return $this->sendError($validator->errors());
        }

        // find the code
        $emailverification = EmailVerification::query()->firstWhere('email', $request['email']);

        if ($request['code'] != $emailverification['code'])
        {
            return $this->sendError(['error' => trans('Code is not valid')]);
        }

        // check if it is not expired : the time is one hour
        if ($emailverification['created_at'] > now()->addHour()) {
            $emailverification->delete();
            return $this->sendError(['error' => trans('password.code_is_expire')]);
        }

        // find user's email
        $user = User::query()->where('email', $emailverification['email']);

        // update user email_verified
        $user->update([
            'email_verified' => 1,
        ]);

        // delete current code
        $emailverification->delete();

        return response()->json([
            'status' => 'success',
            'code' => $emailverification['code'],
            'message' => trans('email.code_is_valid'),
        ], 200);
    }

    public function resendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email']
        ]);

        if ($validator->fails())
        {
            return $this->sendError($validator->errors());
        }

        EventsEmailVerification::dispatch($request->email);

        return $this->sendResponse([]);
    }
}