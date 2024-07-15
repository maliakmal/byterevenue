<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(\App\Http\Middleware\CheckExternalApiToken::class)->group(function () {
    Route::prefix('messages')->group(function (){
        Route::post('/update/sent', [JobsController::class, 'updateSentMessage']);
        Route::post('/update/clicked', [JobsController::class, 'updateClickMessage']);
    });
});
