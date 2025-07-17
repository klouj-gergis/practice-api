<?php


use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use Illuminate\Support\Facades\Route;


/* Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/profile', [AuthController::class, 'getprofile']);
    Route::post('/access-token', [AuthController::class, 'getAccessToken']);
}); */


Route::apiResource('products', ProductController::class)->only(['index', 'show']);

Route::middleware(['auth:sanctum', 'permission:create products', 'isAdmin'])->group(function () {
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
});

Route::middleware(['auth:sanctum', 'permission:create orders', 'isCustomer'])->group(function () {
    Route::apiResource('cart', CartController::class)->except(['show']);
});

Route::middleware(['auth:sanctum', 'permission:create orders','isCustomer'])->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'checkout']);
    Route::get('/orders', [CheckoutController::class, 'orderHestory']);
    Route::get('/orders/{orderId}', [CheckoutController::class, 'showOrder']);
});

include_once __DIR__ . '/auth.php';
