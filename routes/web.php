<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|

Route::get('/', function () {
    return view('welcome');
});
*/


Route::get('/', function () {
    return view('webapp/home');
});

Route::get('/search', function () {
    return view('webapp/list');
});

Route::get('/buy', function () {
    return view('webapp/buy');
});

Route::get('/reader', function () {
    return view('webapp/reader');
});

Route::get('/how-to-pay', function () {
    return view('webapp/howtopay');
});

Route::get('/contact', function () {
    return view('webapp/contact');
});
Route::get('/privacy-policy', function () {
    return view('webapp/privacypolicy');
});

Route::get('/pdf-viewer', function () {
    return view('webapp/pdfviewer');
});


// ADMINER DATABASE MANAGEMENT TOOL
Route::any('adminer', '\Aranyasen\LaravelAdminer\AdminerAutologinController@index');

