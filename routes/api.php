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
    Route::get('/wizardData', [AuthController::class, 'getWizardData']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/validate-token', function (Request $request) {
        $user = $request->user();
        $country = $user->country;
        $role = $user->getRoleNames()->first();
 
        $response = [
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => $user->email,
            'company' => $user->company,
            'nif' => $user->nif,
            'address' => $user->address,
            'city' => $user->city,
            'zip_code' => $user->zip_code,
            'phone' => $user->phone,
            'prefix_id' => $user->prefix_id,
            'code_prefix' => $country->codeISO2 ?? null,
            'role_id' => $role->id ?? null,
            'role' => $role->name ?? null,
            'country_id' => $user->country_id,
            'country' => isset($country) ? __('countries.' . $country->language_field) : null,
            'email_verified_at' => $user->email_verified_at,
        ];
 
        return response()->json($response);
    });
}); 

Route::middleware('web')->group(function () {
    Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle']);
    Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

    Route::get('auth/linkedin', [SocialAuthController::class, 'redirectToLinkedIn']);
    Route::get('auth/linkedin/callback', [SocialAuthController::class, 'handleLinkedInCallback']);
});

//ROLES Y PERMISOS

