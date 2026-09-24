<?php

use App\Http\Controllers\PlayingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Playing
Route::get('playing', [PlayingController::class, 'playing']);
