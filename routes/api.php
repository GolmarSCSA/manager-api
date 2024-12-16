<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;


Route::post('/register', [AuthController::class, 'register']);

//Route::post('/validate-user-first-step', [UserValidationController::class, 'validateUserFirstStep']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('authApiMiddleware')->group(function () {
    //VERIFY
    Route::get('/email/verify-status', [AuthController::class, 'checkVerificationStatus']);
    Route::post('/wizard-step-1', [UserController::class, 'firstStepWizard']);
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/resend-code', [AuthController::class, 'resendCode']);
        Route::post('/verify-code', [AuthController::class, 'verifyCode']);
    });

    //AUTH STATUS
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/validate-token', function (Request $request) {
        return response()->json([
            'valid' => true,
            'user' => $request->user(),
        ]);
    });

}); 

Route::middleware('web')->group(function () {
    Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle']);
    Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

    Route::get('auth/linkedin', [SocialAuthController::class, 'redirectToLinkedIn']);
    Route::get('auth/linkedin/callback', [SocialAuthController::class, 'handleLinkedInCallback']);
});

//ROLES Y PERMISOS

