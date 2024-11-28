<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserValidationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])->middleware('auth:api')->name('verification.resend');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware(['auth:api', 'signed'])->name('verification.verify');
Route::get('/email/verify-status', [AuthController::class, 'checkVerificationStatus'])->middleware('auth:api');
Route::post('/resend-code', [AuthController::class, 'resendCode'])->middleware('throttle:5,1');
Route::post('/verify-code', [AuthController::class, 'verifyCode'])->middleware('throttle:5,1');


//ROLES Y PERMISOS
Route::post('/validate-user-first-step', [UserValidationController::class, 'validateUserFirstStep']);
Route::get('/get-first-step-data', [UserValidationController::class, 'firstStepData']);


Route::get('/test', function () {
    return response()->json(['status' => 'API ERROR'], 500);
});
