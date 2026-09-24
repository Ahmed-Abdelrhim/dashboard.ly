<?php

use App\Http\Controllers\Api\StoreLeadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/playing/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Submit lead
Route::post('/l/leads', StoreLeadController::class)->middleware('throttle:3,1')->name('api.leads.store');
