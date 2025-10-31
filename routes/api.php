<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChunkFileUploadController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Middleware\admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/options/categories', [CategoryController::class, 'category_dropdown']);
Route::get('/options/sub-categories/{category_id}', [CategoryController::class, 'sub_category_dropdown']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/products/{product}/reviews', [ReviewController::class, 'index']);

Route::get('/categories/{category}/products/{product}', [CategoryController::class, 'products']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{productId}', [FavoriteController::class, 'destroy']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);

    Route::post('/products/{product}/reviews', [ReviewController::class, 'store']);
    Route::get('/products/{product}/reviews/{review}', [ReviewController::class, 'show']);
    Route::put('/products/{product}/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/products/{product}/reviews/{review}', [ReviewController::class, 'destroy']);

    // Admin Access Only
    Route::middleware(admin::class)->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        Route::post('init_chunked_upload', [ChunkFileUploadController::class, 'initChunkedUpload']);
        Route::post('upload_chunk', [ChunkFileUploadController::class, 'uploadChunk']);
        Route::post('complete_chunked_upload', [ChunkFileUploadController::class, 'completeChunkedUpload']);
    });
});
