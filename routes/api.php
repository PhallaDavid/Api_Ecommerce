<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\PaymentController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/banners', [BannerController::class, 'store']);
Route::get('/banners', [BannerController::class, 'index']);
Route::put('/banners/{id}', [BannerController::class, 'update']);
Route::delete('/banners/{id}', [BannerController::class, 'destroy']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/payments/create', [PaymentController::class, 'create']);
Route::get('/payment/status/{md5}', [PaymentController::class, 'checkStatus']);
// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
Route::get('/products/search', [App\Http\Controllers\Api\ProductSearchController::class, 'search']);

// Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);
Route::get('/products/promotion', [ProductController::class, 'promotion']);

Route::get('products/category/{id}', [ProductController::class, 'productsByCategory']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('products/{id}/favorite', [ProductController::class, 'addFavorite']);
    Route::delete('products/{id}/favorite', [ProductController::class, 'removeFavorite']);
    Route::middleware('auth:sanctum')->get('/favorites', [ProductController::class, 'favorites']);
    Route::post('products/{id}/cart', [ProductController::class, 'addToCart']);
    Route::delete('products/{id}/cart', [ProductController::class, 'removeFromCart']);
    Route::middleware('auth:sanctum')->get('/cart', [ProductController::class, 'cart']);
});


// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',  [AuthController::class, 'logout']);
    Route::get('/profile',  [AuthController::class, 'profile']);
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
