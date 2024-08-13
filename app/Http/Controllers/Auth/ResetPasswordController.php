<?php

namespace App\Http\Controllers\Auth;

use App\Events\ResetPassword;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ResetPasswordRequest;
use App\Mail\SendCodeResetPassword;
use App\Models\ResetCodePassword;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\error;

class ResetPasswordController extends BaseController
{
    public function userForgotPassword(Request $request) : JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'=>'required|email|exists:users,email',
        ]);

        if ($validator->fails())
        {
            return $this->sendError($validator->errors());
        }

        //Delete all old codes that user send before.
        ResetCodePassword::query()->where('email', $request['email'])->delete();

        //Generate random code
        $request['code'] = mt_rand(100000,999999);

        //Create a new code
        $codeData = ResetCodePassword::query()->create($request->all());

        //Send email to user
        Mail::to($request['email'])->send(new SendCodeResetPassword($codeData['code']));

        return $this->sendResponse([]);
    }

    public function userCheckCode(Request $request) : JsonResponse
    {
        $validator = Validator::make($request->all(), (new ResetPasswordRequest())->rules());
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        // find the email
        $passwordReset = ResetCodePassword::query()->where('email', $request['email'])->where('code',$request['code'])->first();
        if (is_null($passwordReset))
        {
            return $this->sendError(['error' => 'code is invalid']);
        }

        // Check if it is not expired: the time is one hour
        if ($passwordReset['created_at'] > now()->addHour()){
            $passwordReset->delete();
            return $this->sendError(['error' => trans('passwords.code_is_expire')]);
        }

        return $this->sendResponse([]);
    }

    public function userResetPassword(Request $request) : JsonResponse
    {
        // Validate the data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:reset_code_passwords,email',
            'password' => ['required','confirmed'],
        ]);

        if ($validator->fails())
        {
            return $this->sendError($validator->errors());
        }

        // delete old codes
        ResetCodePassword::query()->where('email', $request['email'])->delete();

        // find user by email
        $user = User::query()->where('email', $request['email'])->first();

        //update user password
        $user ->update([
            'password' => bcrypt($request['password'])
        ]);

        return $this->sendResponse([]);
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

        ResetPassword::dispatch($request->email);

        return $this->sendResponse([]);
    }
}
