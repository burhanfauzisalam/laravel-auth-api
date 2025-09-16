<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/ulogin',     [AuthController::class, 'ulogin']);

Route::middleware('auth:api')->group(function () {
    Route::get('/cek-token',     [AuthController::class, 'cekToken']);
    Route::get('/me',     [AuthController::class, 'me']);
    Route::post('/logout',[AuthController::class, 'logout']);
    Route::post('/refresh',[AuthController::class, 'refresh']);
});