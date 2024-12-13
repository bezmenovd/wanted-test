<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers;

Route::group(['middleware' => 'auth.basic'], function() {
    Route::post('/import', Controllers\ImportController::class);
});

Route::get('/list', Controllers\ListController::class);
