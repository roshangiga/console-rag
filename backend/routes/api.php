<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Directory routes
    Route::get('/directories', [DirectoryController::class, 'index']);
    Route::get('/directories/{id}/contents', [DirectoryController::class, 'getContents']);
    Route::post('/directories', [DirectoryController::class, 'store']);
    Route::put('/directories/{id}', [DirectoryController::class, 'update']);
    Route::delete('/directories/{id}', [DirectoryController::class, 'destroy']);

    // Document routes
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{id}', [DocumentController::class, 'show']);
    Route::put('/documents/{id}', [DocumentController::class, 'update']);
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);
    Route::post('/documents/{id}/process', [DocumentController::class, 'process']);

    // Tag routes
    Route::get('/tags', [TagController::class, 'index']);
});
