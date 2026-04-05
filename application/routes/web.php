<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    Log::info('Hello World 123');
    return view('welcome');
});

// Route::get('/horizon', function () {
//     return redirect('/horizon/dashboard');
// });
