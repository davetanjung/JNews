<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewsController;

Route::get('/', [NewsController::class, 'index'])->name('home');
Route::get('/article/{id}', [NewsController::class, 'show'])->name('article.show');

Route::prefix('api')->group(function () {
    Route::get('news/top-headlines', [NewsController::class, 'topHeadlines']);
    Route::get('news/search', [NewsController::class, 'search']);
});