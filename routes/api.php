<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\UserValidationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/email/verify-status', [AuthController::class, 'checkVerificationStatus'])->middleware('auth:api');
Route::post('/resend-code', [AuthController::class, 'resendCode'])->middleware('throttle:5,1', 'auth:api');
Route::post('/verify-code', [AuthController::class, 'verifyCode'])->middleware('throttle:5,1', 'auth:api');

Route::middleware('web')->group(function () {
    Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle']);
    Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

    Route::get('auth/linkedin', [SocialAuthController::class, 'redirectToLinkedIn']);
    Route::get('auth/linkedin/callback', [SocialAuthController::class, 'handleLinkedInCallback']);
});

//ROLES Y PERMISOS
Route::post('/validate-user-first-step', [UserValidationController::class, 'validateUserFirstStep']);
Route::get('/get-first-step-data', [UserValidationController::class, 'firstStepData']);
