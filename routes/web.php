<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewsController;

// Registration Routes
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Login Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Logout Route
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', [NewsController::class, 'index'])->name('home');
Route::get('/article/{id}', [NewsController::class, 'show'])->name('article.show');

Route::prefix('api')->group(function () {
    Route::get('news/top-headlines', [NewsController::class, 'topHeadlines']);
    Route::get('news/search', [NewsController::class, 'search']);
});

Route::get('/news/summary', [NewsController::class, 'getSummary'])->name('news.summary');