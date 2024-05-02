<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Servicecontroller;
use App\Http\Controllers\{
    Auth\LoginController,
    Auth\LogoutController,
    Auth\RegisterController,
    
    
};
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\EmailVerificationController;
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

//login
Route::post('/login', [LoginController::class, 'login']);

//reset password
Route::post('user/password/email',[ResetPasswordController::class,'userForgotPassword']);
Route::post('user/password/check', [ResetPasswordController::class, 'userCheckCode']);
Route::post('user/password/reset',[ResetPasswordController::class,'userResetPassword']);

Route::middleware('auth:sanctum')->group (function(){
    Route::get('/logout', [LogoutController::class, 'logout']);
    
Route::middleware(['auth:sanctum', 'can:isAdministrator'])->group(function() {
    Route::post('/services/add', [serviceController::class, 'addservice'])->name('services.add');
    Route::get('/allservice', [Servicecontroller::class, 'getallservices']);
    Route::get('/service_categoury/{categoury}', [Servicecontroller::class, 'showcategouryser']);

    });

    Route::middleware(['auth:sanctum', 'can:isSponsor'])->group(function() {

    });

    Route::middleware(['auth:sanctum', 'can:isClient'])->group(function() {

    });
});
