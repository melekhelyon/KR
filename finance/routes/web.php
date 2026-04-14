<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/{page}', function () {
    return view('main');
})->where('page', '.*');