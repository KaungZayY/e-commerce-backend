<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChunkFileUploadController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/options/categories', [CategoryController::class, 'category_dropdown']);
Route::get('/options/sub-categories/{category_id}', [CategoryController::class, 'sub_category_dropdown']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Admin Access Only
    Route::middleware(admin::class)->group(function () {
        Route::apiResource('/products', ProductController::class);

        Route::post('init_chunked_upload', [ChunkFileUploadController::class, 'initChunkedUpload']);
        Route::post('upload_chunk', [ChunkFileUploadController::class, 'uploadChunk']);
        Route::post('complete_chunked_upload', [ChunkFileUploadController::class, 'completeChunkedUpload']);
    });
});