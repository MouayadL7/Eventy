<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;

use App\Models\EmailVerification;
use App\Models\User;

use Illuminate\Http\JsonResponse;


use Illuminate\Support\Facades\Validator;

class EmailVerificationController extends BaseController
{
    public function userCheckCode(Request $request) : JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:email_verifications,email',
            'code' => 'required|string|exists:email_verifications,code',
        ],[
            'email.exists' => 'Email is not valid',
            'code.exists' => 'Code is not valid',
        ]);

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
}