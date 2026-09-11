<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CatalogueController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\MockPaymentController;
use App\Http\Controllers\Api\CartController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('rate.limit:10')->group(function (): void {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    Route::post('register', [RegisterController::class, 'register']);
    Route::get('verify-email/{token}', [RegisterController::class, 'verify']);

    Route::post('forgot-password', [PasswordResetController::class, 'forgotPassword']);
    Route::get('forgot-password/validate', [PasswordResetController::class, 'validateToken']);
    Route::post('reset-password', [PasswordResetController::class, 'resetPassword']);
});

Route::middleware(['auth:api', 'jwt.blacklist', 'rate.limit:20'])->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('complete-profile', [RegisterController::class, 'completeProfile']);
    });

    Route::apiResource('users', UserController::class);
    Route::apiResource('catalogues', CatalogueController::class);
    Route::apiResource('categories', CategoryController::class);

    // Checkout
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('auth:api');

    // Mock payment (nonaktifkan di production via middleware atau env check)
    Route::prefix('mock-payment')->group(function () {
        Route::get('/{orderId}',       [MockPaymentController::class, 'show'])
            ->name('mock.payment.show');

        Route::post('/{orderId}/pay',  [MockPaymentController::class, 'approve'])
            ->name('mock.payment.approve');

        Route::post('/{orderId}/fail', [MockPaymentController::class, 'reject'])
            ->name('mock.payment.reject');
    });

    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'show']);
        Route::delete('/', [CartController::class, 'clear']);
        Route::post('/items', [CartController::class, 'addItem']);
        Route::patch('/items/{cartItem}', [CartController::class, 'updateItem']);
        Route::delete('/items/{cartItem}', [CartController::class, 'removeItem']);
    });
});