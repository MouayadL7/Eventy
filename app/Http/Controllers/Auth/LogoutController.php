<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class LogoutController extends BaseController
{
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete;
        return $this->sendResponse([]);
    }
}
