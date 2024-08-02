<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Servicecontroller;
use App\Http\Controllers\{
    AbroveController,
    Auth\LoginController,
    Auth\LogoutController,
    Auth\RegisterController,
    Auth\EmailVerificationController,
    Auth\ResetPasswordController,
    CartController,
    CategouryController,
    ConversationController,
    FavouriteController,
    MessageController,
    OrderController,
    OrderStateController,
    RatingController,
    SearchController,
};
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Payment\BudgetController;
use App\Http\Controllers\Report\ReportsController;
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

    Route::get('order', [OrderController::class, 'index']);


    Route::resource('conversation', ConversationController::class);
    Route::resource('message', MessageController::class);

    // Notification
    Route::get('notifications', [NotificationController::class, 'myNotifications']);

    Route::middleware(['auth:sanctum', 'can:isAdministrator'])->group(function() {
        // Report
        Route::post('report/reply', [ReportsController::class, 'reply']);
        Route::get('report/newReports', [ReportsController::class, 'newReports']);

        // Budget
        Route::post('budget/charge', [BudgetController::class, 'charge']);
        Route::get('budget/charge/search', [BudgetController::class, 'search']);

        //Add Servic
        Route::post('add', [Servicecontroller::class, 'addservice']);

        // Abrove
        Route::prefix('abrove')->group(function () {
                Route::get('/', [AbroveController::class, 'index']);
                Route::get('{id}', [AbroveController::class, 'show']);
                Route::post('reply', [AbroveController::class, 'reply']);
        });

        // Service
        Route::get('allservice', [Servicecontroller::class, 'getallservices']);
    });



    Route::middleware(['auth:sanctum', 'can:isSponsor'])->group(function() {
        // Order State
        Route::post('order/{orderId}/state', [OrderStateController::class, 'updateOrderState']);
    });



    Route::middleware(['auth:sanctum', 'can:isClient'])->group(function() {

        // Budget routes
        Route::prefix('budget')->group(function () {
            Route::get('details', [BudgetController::class, 'get_budget']);
        });

        // Report
        Route::post('report/create', [ReportsController::class, 'store']);

        //services
        Route::prefix('services')->group(function () {

            Route::get('service_categoury/{categoury}', [Servicecontroller::class, 'showcategouryser']);

       //search and filter
            Route::get('search', [SearchController::class, 'search']);
            Route::get('filter', [SearchController::class, 'filter']);
        });

        //categories
        Route::prefix('categories')->group(function () {
            Route::get('/', [CategouryController::class, 'index']);
            Route::get('/{categoury}', [CategouryController::class, 'show']);
        });

        // Rating
        Route::prefix('rating')->group(function () {
            Route::get('{id}', [RatingController::class, 'sponsorRate']);
            Route::post('store', [RatingController::class, 'store']);
        });

        Route::prefix('favourites')->group(function () {
        // Add a service to favorites
            Route::post('add', [FavouriteController::class, 'add']);
        // Remove a service from favorites
            Route::delete('remove/{id}', [FavouriteController::class, 'remove']);
        // List favorite services
            Route::get('/', [FavouriteController::class, 'list']);
        });

        // Cart routes
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('cart.index');
            Route::post('add', [CartController::class, 'store']);
            Route::delete('/{id}', [CartController::class, 'destroy'])->name('cart.destroy');
        });

        // Order routes
        Route::prefix('order')->group(function () {
            Route::delete('/{id}/cancel', [OrderController::class, 'cancelOrder'])->name('order.cancel');
            Route::get('confirm', [OrderController::class, 'confirm']);
        });

        //booking
        Route::prefix('bookings')->group(function () {
            Route::get('dates/{serviceId}', [CartController::class, 'getBookedDates']);
        });
    });
});
