<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Route::middleware(\App\Http\Middleware\CheckExternalApiToken::class)->group(function () {

    Route::prefix('messages')->group(function (){
        Route::post('/update-by-file/sent', [\App\Http\Controllers\Api\BroadcastLogController::class, 'updateSentMessage']);
        Route::post('/update/sent', [JobsController::class, 'updateSentMessage']);
        Route::post('/update/clicked', [JobsController::class, 'updateClickMessage']);
    });

    Route::prefix('blacklist-numbers')->group(function (){
        Route::post('/upload', [\App\Http\Controllers\Api\BlackListNumberController::class, 'updateBlackListNumber']);
    });
    Route::prefix('jobs')->group(function (){
        Route::post('/generate-csv', [\App\Http\Controllers\JobsController::class, 'index']);
    });

    Route::prefix('batch_files')->group(function (){
        Route::post('/', [\App\Http\Controllers\Api\BatchFileController::class, 'index']);
        Route::post('/get-form-content-from-campaign', [\App\Http\Controllers\Api\BatchFileController::class, 'getFormContentFromCampaign']);
    });
//});
