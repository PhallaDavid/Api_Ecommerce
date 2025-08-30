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
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ReviewController;

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);
Route::post('/products/{id}/reviews', [ReviewController::class, 'store'])->middleware('auth:sanctum');
// Banners
Route::apiResource('banners', BannerController::class)->except(['show']);

// Orders & Delivery
Route::post('/orders/{orderId}/location', [DeliveryController::class, 'updateLocation']);
Route::get('/orders/{orderId}/location', [DeliveryController::class, 'getLocation']);

// Categories
Route::apiResource('categories', CategoryController::class);

// Products
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/promotion', [ProductController::class, 'promotion']);
    Route::get('/search', [ProductSearchController::class, 'search']);

    // **Important:** category route must come before {id}
    Route::get('/category/{id}', [ProductController::class, 'productsByCategory']);
    Route::get('/{id}', [ProductController::class, 'show']);

    Route::post('/', [ProductController::class, 'store']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
});

// Protected routes (require Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout',  [AuthController::class, 'logout']);
    Route::get('/profile',  [AuthController::class, 'profile']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::get('/user', fn(Request $request) => $request->user());

    // Favorites
    Route::post('products/{productId}/favorite', [ProductController::class, 'addFavorite']);
    Route::delete('products/{productId}/favorite', [ProductController::class, 'removeFavorite']);
    Route::get('products/favorites', [ProductController::class, 'favorites']);

    // Cart
    Route::post('products/{id}/cart', [ProductController::class, 'addToCart']);
    Route::delete('products/{id}/cart', [ProductController::class, 'removeFromCart']);
    Route::get('/cart', [ProductController::class, 'cart']);
});

// Payments
Route::post('/payments/create', [PaymentController::class, 'create']);
Route::get('/payment/status/{md5}', [PaymentController::class, 'checkStatus']);

// Create order (protected)
Route::middleware('auth:sanctum')->post('/orders', [OrderController::class, 'createOrder']);
