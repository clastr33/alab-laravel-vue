<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ResultsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/login', '/');

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware('auth:api')->group(function () {
    Route::get('/results', [ResultsController::class, 'index']);
});
