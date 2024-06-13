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
    CartController,
    CategouryController,
    FavouriteController,
    OrderController,
    RatingController,
    SearchController,
};
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



    Route::middleware(['auth:sanctum', 'can:isAdministrator'])->group(function() {
        // Report
        Route::post('report/reply', [ReportsController::class, 'reply']);
        Route::get('report/newReports', [ReportsController::class, 'newReports']);

        // Budget
        Route::post('budget/charge', [BudgetController::class, 'charge']);
    });

    Route::middleware(['auth:sanctum', 'can:isSponsor'])->group(function() {

    });



    Route::middleware(['auth:sanctum', 'can:isClient'])->group(function() {

        // Budget routes
        Route::prefix('budget')->group(function () {
            Route::get('details', [BudgetController::class, 'get_budget']);
            Route::post('pay', [BudgetController::class, 'pay']);
        });

        // Report
        Route::post('report/create', [ReportsController::class, 'store']);

        //services
        Route::prefix('services')->group(function () {
            Route::get('allservice', [Servicecontroller::class, 'getallservices']);
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

        //rating
        Route::post('organizers/{sponsor}/ratings', [RatingController::class, 'store']);
        Route::get('rates/{sponsor}', [RatingController::class, 'sponserRate']);

        Route::prefix('favorites')->group(function () {
            // Add a service to favorites
            Route::post('add', [FavouriteController::class, 'add']);
            // Remove a service from favorites
            Route::post('remove', [FavouriteController::class, 'remove'])->name('favorites.remove');
            // List favorite services
            Route::get('/', [FavouriteController::class, 'list'])->name('favorites.list');
        });

        // Cart routes
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('cart.index');
            Route::post('add', [CartController::class, 'store']);
            Route::get('/{id}', [CartController::class, 'show'])->name('cart.show');
            Route::post('delete/{id}', [CartController::class, 'destroy'])->name('cart.destroy');
            Route::post('confirm', [OrderController::class, 'confirm'])->name('cart.confirm');
        });

        // Order routes
        Route::prefix('order')->group(function () {
            Route::delete('/{order}/cancel', [OrderController::class, 'cancelOrder'])->name('order.cancel');
            Route::post('/{orderId}/state', [OrderController::class, 'updateOrderState'])->name('order.updateState');
        });

        //booking
        Route::prefix('bookings')->group(function () {
            Route::get('dates/{serviceId}', [CartController::class, 'getBookedDates']);
        });
    });
});
