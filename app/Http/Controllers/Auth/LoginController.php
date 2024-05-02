<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Notifications\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends BaseController
{
    public function login(Request $request) :JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'email'    => 'required|email|exists:users,email',
            'password' => 'required',
            'device_token' => 'required|string',
        ],[
            'email.exists' => 'email is invalid',
            'password.min' =>'password must be at least 8 characters'
        ]);

        if($validator->fails())
        {
            return $this->sendError($validator->errors());
        }

        if (Auth::attempt($request->only(['email', 'password']))) {
            $user = Auth::user();

            if(!$user['email_verified'])
            {
                return $this->sendError(['error' => 'email is invalid']);
            }
        
            $userable = $user->userable;
            $userable['email']       = $user['email'];
            $userable['phone']       = $user['phone'];
            $userable['role_id']     = $user['role_id'];
            $userable['accesstoken'] = $request->user()->createToken('access token')->plainTextToken;
            $userable['id']          = $user['id'];

            return $this->sendResponse($userable);
           $request->User()->notify(new UserNotification('Sarah','SARSARA'));
        }

        return $this->sendError(['error' => 'Unauthorized']);
    }
}