<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ChatController;

Route::middleware('api')->post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

    Route::get('/cart', [CartController::class, 'getCart'])->name('cart.get');
    Route::post('/cart/add', [CartController::class, 'addItem'])->name('cart.add');
    Route::post('/cart/remove', [CartController::class, 'removeItem'])->name('cart.remove');

    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

    // ===== 聊天室 API =====
    Route::get('/chat/users', [ChatController::class, 'getUsers']); // 好友列表
    Route::post('/chat/room', [ChatController::class, 'getOrCreateRoom']); // 建立或取得聊天室
    Route::get('/chat/messages/{roomId}', [ChatController::class, 'getMessages']);
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    Route::post('/chat/read/{roomId}', [ChatController::class, 'markAsRead']);
    Route::get('/chat/unread-counts', [ChatController::class, 'unreadCounts']);
});

