<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\UserValidationController;


Route::post('/register', [AuthController::class, 'register']);
Route::get('/get-first-step-data', [UserValidationController::class, 'firstStepData']);
Route::post('/validate-user-first-step', [UserValidationController::class, 'validateUserFirstStep']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    //VERIFY
    Route::get('/email/verify-status', [AuthController::class, 'checkVerificationStatus'])->middleware('auth:api');
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/resend-code', [AuthController::class, 'resendCode']);
        Route::post('/verify-code', [AuthController::class, 'verifyCode']);
    });

    //AUTH STATUS
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::post('/logout', [AuthController::class, 'logout']);

}); 

Route::middleware('web')->group(function () {
    Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle']);
    Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

    Route::get('auth/linkedin', [SocialAuthController::class, 'redirectToLinkedIn']);
    Route::get('auth/linkedin/callback', [SocialAuthController::class, 'handleLinkedInCallback']);
});

//ROLES Y PERMISOS

