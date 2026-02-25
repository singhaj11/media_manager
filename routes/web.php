<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MediaController;

Route::get('/', function () {
    return redirect()->route('media.index');
});

Route::resource('media', MediaController::class)->only(['index', 'store', 'update', 'destroy']);
