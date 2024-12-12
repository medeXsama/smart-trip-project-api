<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BigQueryController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', function () {
    return view('register');
})->name('register');

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::post('/add-user', [BigQueryController::class, 'insertDataToBigQuery']);
Route::post('/login', [BigQueryController::class, 'loginUser'])->name('login.user');
Route::get('/get-types', [BigQueryController::class, 'getTypes']);
Route::get('/get-keywords', [BigQueryController::class, 'getKeywords']);

