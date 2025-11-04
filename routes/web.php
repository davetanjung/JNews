<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewsController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/news/search', [NewsController::class, 'search']);
Route::get('/news/top-headlines', [NewsController::class, 'topHeadlines']);

