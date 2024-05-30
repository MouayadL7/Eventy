<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Servicecontroller;
use App\Http\Controllers\{
    Auth\LoginController,
    Auth\LogoutController,
    Auth\RegisterController,
    Auth\EmailVerificationController,
    Auth\ResetPasswordController,
};
use App\Http\Controllers\Payment\BudgetController;
use App\Models\service;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


//register
Route::post('/register', [RegisterController::class, 'register']);

//EmailVerification
Route::post('user/email/check',[EmailVerificationController::class,'userCheckCode']);
Route::post('resendcode',[EmailVerificationController::class,'resendCode']);


//login
Route::post('/login', [LoginController::class, 'login']);

//reset password
Route::post('user/password/email',[ResetPasswordController::class,'userForgotPassword']);
Route::post('user/password/check', [ResetPasswordController::class, 'userCheckCode']);
Route::post('user/password/reset',[ResetPasswordController::class,'userResetPassword']);
Route::post('user/password/resendcode', [ResetPasswordController::class, 'resendCode']);

//logout
Route::middleware('auth:sanctum')->group (function(){
    Route::post('/logout', [LogoutController::class, 'logout']);



Route::middleware(['auth:sanctum', 'can:isAdministrator'])->group(function() {

    });

    Route::middleware(['auth:sanctum', 'can:isSponsor'])->group(function() {

    });



    Route::middleware(['auth:sanctum', 'can:isClient'])->group(function() {
        
        // Budget routes
        Route::prefix('budget')->group(function () {
            Route::get('details', [BudgetController::class, 'get_budget']);
            Route::post('pay', [BudgetController::class, 'pay']);
            Route::post('charge', [BudgetController::class, 'charge']);
        });

    });
});
