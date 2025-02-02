<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register',  [UserController::class, 'store']);
Route::post('/refresh-token', [UserController::class, 'refresh']);

Route::post('/login',  [UserController::class, 'index']);