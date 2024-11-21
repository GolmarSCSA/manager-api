<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//ROLES Y PERMISOS
Route::get('/selectableRoles', [RolesController::class, 'getSelectableRoles']);

Route::get('/test', function () {
    return response()->json(['status' => 'API ERROR'], 500);
});
