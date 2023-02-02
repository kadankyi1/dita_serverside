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
| is assigned the "api" middleware group. Enjoy building your .y1YLhBTZQoe3H!c API!
|
*/


// GET LOGIN CODE
Route::post('/v1/user/send-login-code',[App\Http\Controllers\version1\UserController::class, 'sendLoginVerificationCode']);

// USE LOGIN CODE TO LOGIN
Route::post('/v1/user/verify-login-code',[App\Http\Controllers\version1\UserController::class, 'verifyLoginCode']);

// GET BOOK LISTING
Route::middleware('auth:api')->post('/v1/user/get-books', [App\Http\Controllers\version1\UserController::class, 'getBookListing']);

// GET BOOK SUMMARIES LISTING
Route::middleware('auth:api')->post('/v1/user/get-books-summaries', [App\Http\Controllers\version1\UserController::class, 'getBookSummariesListing']);

// CONTACT DITA TEAM
Route::middleware('auth:api')->post('/v1/user/send-message', [App\Http\Controllers\version1\UserController::class, 'contactDitaTeam']);

// GET PAYMENT URL
Route::post('/v1/user/get-payment-url',[App\Http\Controllers\version1\UserController::class, 'getPaymentUrl']);

// VERIFY PAYMENT URL
Route::post('/v1/user/verify-payment',[App\Http\Controllers\version1\UserController::class, 'verifyPayStackPayment']);

// RECORD PURCHSE
Route::middleware('auth:api')->post('/v1/user/record-payment', [App\Http\Controllers\version1\UserController::class, 'recordPurchase']);
