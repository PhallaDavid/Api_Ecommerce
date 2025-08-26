<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductSearchController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Api\OrderController;

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

// Banners
Route::apiResource('banners', BannerController::class)->except(['show']);

// Categories
Route::apiResource('categories', CategoryController::class);

// Products
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/promotion', [ProductController::class, 'promotion']);
    Route::get('/search', [ProductSearchController::class, 'search']);
    Route::get('/category/{id}', [ProductController::class, 'productsByCategory']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::post('/', [ProductController::class, 'store']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->post('/orders', [OrderController::class, 'createOrder']);

Route::post('/payments/create', [PaymentController::class, 'create']);
Route::get('/payment/status/{md5}', [PaymentController::class, 'checkStatus']);

// Protected routes (require Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout',  [AuthController::class, 'logout']);
    Route::get('/profile',  [AuthController::class, 'profile']);
    Route::get('/user', fn(Request $request) => $request->user());

    // Favorites & Cart
    Route::post('products/{id}/favorite', [ProductController::class, 'addFavorite']);
    Route::delete('products/{id}/favorite', [ProductController::class, 'removeFavorite']);
    Route::get('/favorites', [ProductController::class, 'favorites']);
    Route::post('products/{id}/cart', [ProductController::class, 'addToCart']);
    Route::delete('products/{id}/cart', [ProductController::class, 'removeFromCart']);
    Route::get('/cart', [ProductController::class, 'cart']);
});
