<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\LoginRequest;
use App\Models\DeviceToken;
use App\Notifications\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends BaseController
{
    public function login(Request $request) :JsonResponse
    {
        $loginRequest = new LoginRequest();
        $validator = Validator::make($request->all(), $loginRequest->rules($request));

        if($validator->fails())
        {
            return $this->sendError($validator->errors());
        }

        if (Auth::attempt($request->only(['email', 'password']))) {
            $user = Auth::user();

            if(!$user['email_verified'])
            {
                return $this->sendError(['error' => 'email is not verified']);
            }

            if ($request->is_client)
            {
                DeviceToken::create([
                    'user_id'      => $user->id,
                    'device_token' => $request->device_token,
                ]);
            }

            $userable = $user->userable;
            $userable['email']       = $user['email'];
            $userable['phone']       = $user['phone'];
            $userable['role_id']     = $user['role_id'];
            $userable['accesstoken'] = $request->user()->createToken('access token')->plainTextToken;
            $userable['id']          = $user['id'];

            $request->user()->notify(new UserNotification('Sarah','SARSARA'));

            return $this->sendResponse($userable);
        }

        return $this->sendError(['error' => 'Unauthorized']);
    }
}
