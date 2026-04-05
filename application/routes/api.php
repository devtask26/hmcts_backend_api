<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('tasks')->group(function () {
    Route::get('/',        [TaskController::class, 'index']);
    Route::get('/{id}',    [TaskController::class, 'show']);
    Route::post('/',       [TaskController::class, 'store']);
    Route::put('/{id}',    [TaskController::class, 'update']);
    Route::delete('/{id}', [TaskController::class, 'destroy']);
});