<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Device\DeviceController;
use App\Http\Controllers\Api\Device\RecipientController;
use App\Http\Controllers\Api\GiftGive\ButtonPressController;
use App\Http\Controllers\Api\GiftGive\GiftGiverController;
use App\Http\Controllers\Api\GiftGive\VoiceMessageController;
use Illuminate\Support\Facades\Route;






Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::get('gift-givers', [GiftGiverController::class, 'index']);
    Route::post('gift-givers', [GiftGiverController::class, 'store']);

    Route::post('presses', [ButtonPressController::class, 'store']);

    Route::get('voice-messages', [VoiceMessageController::class, 'index']);
    Route::post('voice-messages', [VoiceMessageController::class, 'store']);
    Route::post('device-name', [DeviceController::class, 'store']);
    Route::delete('device-name', [DeviceController::class, 'destroy']);
    Route::post('recipient-name', [RecipientController::class, 'store']);
});
