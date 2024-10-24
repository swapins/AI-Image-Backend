<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Broadcast;

Route::middleware(['auth:sanctum','addUserRole'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/upload-image', [ImageController::class, 'upload'])->middleware('auth:sanctum');
Route::get('/generate-variations/{imageId}', [ImageController::class, 'generateImageVariations']);
Route::get('/user-images',[ImageController::class,'getUserImages']);
