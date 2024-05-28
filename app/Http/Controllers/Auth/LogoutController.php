<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LogoutController extends BaseController
{
    public function logout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_token' => Rule::requiredIf(function() use ($request) {
                return Gate::allows('isClient', Auth::user());
            })
        ]);

        if ($validator->fails())
        {
            return $this->sendError($validator->errors());
        }
        
        if ($request->input('device_token')) {
            DeviceToken::query()->where('device_token', $request->device_token)->delete();
        }
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse([]);
    }
}
