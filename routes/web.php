<?php

use App\Models\version1\Book;
use Illuminate\Support\Facades\File;
use App\Models\version1\Transaction;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\version1\UtilController;

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

Route::get('storage/{trxref}/{reference}/{filename}', function ($filename)
{
    $path = storage_path('app/public/books_summaries/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});

// ADMINER DATABASE MANAGEMENT TOOL
Route::any('adminer', '\Aranyasen\LaravelAdminer\AdminerAutologinController@index');

