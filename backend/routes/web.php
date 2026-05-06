<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Default auth middleware expects a route named "login" for browser redirects.
Route::get('/login', fn () => redirect('/'))->name('login');
