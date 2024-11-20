<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// group auth routes for api
Route::post('login', [\App\Http\Controllers\Api\AuthApiController::class, 'login']);
Route::post('register', [\App\Http\Controllers\Api\AuthApiController::class, 'register']);
Route::post('logout', [\App\Http\Controllers\Api\AuthApiController::class, 'logout']);
Route::post('forgot-password', [\App\Http\Controllers\Api\AuthApiController::class, 'forgotPassword']);
Route::post('reset-password', [\App\Http\Controllers\Api\AuthApiController::class, 'resetPassword']);
Route::post('refresh', [\App\Http\Controllers\Api\AuthApiController::class, 'refresh']);
Route::get('me', [\App\Http\Controllers\Api\AuthApiController::class, 'me'])->middleware('auth:sanctum');
Route::get('user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// group routes that require api token for internal requests
Route::middleware([/*\App\Http\Middleware\CheckExternalApiToken::class, */])->group(function () {
    Route::prefix('messages')->group(function () {
        Route::post('update-by-file/sent', [\App\Http\Controllers\Api\BroadcastLogApiController::class, 'updateSentMessage']);
        Route::post('update/sent', [\App\Http\Controllers\Api\JobsApiController::class, 'updateSentMessage']);
        Route::post('update/clicked', [\App\Http\Controllers\Api\JobsApiController::class, 'updateClickMessage']);
    });

    Route::prefix('blacklist-numbers')->group(function () {
        Route::post('upload', [\App\Http\Controllers\Api\BlackListNumberApiController::class, 'updateBlackListNumber']);
    });

    Route::prefix('jobs')->group(function () {
        Route::post('internal/generate-csv', [\App\Http\Controllers\Api\JobsApiController::class, 'postIndex']);
        Route::post('internal/regenerate-csv', [\App\Http\Controllers\Api\JobsApiController::class, 'regenerateUnsent']);
    });

    Route::prefix('campaigns')->group(function () {
        Route::post('ignore', [\App\Http\Controllers\Api\CampaignApiController::class, 'markAsIgnoreFromQueue']);
        Route::post('unignore', [\App\Http\Controllers\Api\CampaignApiController::class, 'markAsNotIgnoreFromQueue']);
    });

    Route::prefix('batch_files')->group(function () {
        Route::post('/', [\App\Http\Controllers\Api\BatchFileApiController::class, 'index']);
        Route::post('check-status', [\App\Http\Controllers\Api\BatchFileApiController::class, 'checkStatus']);
        Route::post('get-form-content-from-campaign', [\App\Http\Controllers\Api\BatchFileApiController::class, 'getFormContentFromCampaign']);
    });
});

// group routes that require auth:sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    Route::any('dashboard', [\App\Http\Controllers\Api\DashboardController::class, 'indexApi']);
    Route::get('introductory/disable', [\App\Http\Controllers\Api\DashboardController::class, 'disableIntroductoryApi']);
    Route::get('json-data-feed', [\App\Http\Controllers\Api\DataFeedController::class, 'getDataFeedApi']);

    Route::controller(\App\Http\Controllers\Api\AccountsController::class)->group(function () {
        Route::get('accounts/', 'indexApi');
        Route::get('accounts/{id}', 'showApi');
        Route::get('tokens', 'showTokensApi');
    });

    Route::get('data-source/info', [\App\Http\Controllers\Api\ContactApiController::class, 'contactsInfo']);
    Route::resource('data-source', \App\Http\Controllers\Api\ContactApiController::class);

    // ControllerApi methods
    Route::resource('simcards', \App\Http\Controllers\Api\SimcardApiController::class);
    Route::resource('clients', \App\Http\Controllers\Api\ClientApiController::class);
    Route::get('mark-processed/{id}', [\App\Http\Controllers\Api\CampaignApiController::class, 'markAsProcessed'])->name('campaigns.markProcessed');
    Route::get('{id}/stats', 'campaignStats'); // \App\Http\Controllers\Api\CampaignApiController
    Route::resource('campaigns', \App\Http\Controllers\Api\CampaignApiController::class);
    Route::resource('recipient_lists', \App\Http\Controllers\Api\RecipientsListApiController::class);

    Route::post('broadcast_batches', [\App\Http\Controllers\Api\BroadcastBatchController::class, 'storeApi']);
    Route::get('broadcast_batches/{id}', [\App\Http\Controllers\Api\BroadcastBatchController::class, 'showApi']);
    Route::post('broadcast_batches/mark_as_processed/{id}', [\App\Http\Controllers\Api\BroadcastBatchController::class,'markAsProcessedApi']);
});

// public routes, without auth
Route::prefix('areas')->name('api.areas.')->group(function () {
    Route::get('get-all-provinces', [\App\Http\Controllers\Api\AreasApiController::class, 'getAllProvinces']);
    Route::get('get-all-cities', [\App\Http\Controllers\Api\AreasApiController::class, 'getAllCities']);
    Route::get('cities-by-province/{province}', [\App\Http\Controllers\Api\AreasApiController::class, 'citiesByProvince']);
});
