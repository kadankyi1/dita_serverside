<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\version1\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// GET LOGIN CODE
Route::post('/v1/user/send-login-code',[App\Http\Controllers\version1\UserController::class, 'sendLoginVerificationCode']);

// USE LOGIN CODE TO LOGIN
Route::post('/v1/user/verify-login-code',[App\Http\Controllers\version1\UserController::class, 'verifyLoginCode']);