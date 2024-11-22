<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\BroadcastLogApiController;
use App\Http\Controllers\Api\JobsApiController;
use App\Http\Controllers\Api\BlackListNumberApiController;
use App\Http\Controllers\Api\CampaignApiController;
use App\Http\Controllers\Api\BatchFileApiController;
use App\Http\Controllers\Api\AccountsApiController;
use App\Http\Controllers\Api\ContactApiController;
use App\Http\Controllers\Api\SimcardApiController;
use App\Http\Controllers\Api\ClientApiController;
use App\Http\Controllers\Api\RecipientsListApiController;
use App\Http\Controllers\Api\BroadcastBatchApiController;
use App\Http\Controllers\Api\AreasApiController;


// group auth routes for api
Route::post('login', [AuthApiController::class, 'login']);
Route::post('register', [AuthApiController::class, 'register']);
Route::post('logout', [AuthApiController::class, 'logout']);
Route::post('forgot-password', [AuthApiController::class, 'forgotPassword']);
Route::post('reset-password', [AuthApiController::class, 'resetPassword']);
Route::post('refresh', [AuthApiController::class, 'refresh']);
Route::get('me', [AuthApiController::class, 'me'])->middleware('auth:sanctum');
Route::get('user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// group routes that require api token for internal requests
Route::middleware([/*\App\Http\Middleware\CheckExternalApiToken::class, */])->group(function () {
    Route::prefix('messages')->group(function () {
        Route::post('update-by-file/sent', [BroadcastLogApiController::class, 'updateSentMessage']);
        Route::post('update/sent', [JobsApiController::class, 'updateSentMessage']);
        Route::post('update/clicked', [JobsApiController::class, 'updateClickMessage']);
    });

    Route::prefix('blacklist-numbers')->group(function () {
        Route::post('upload', [BlackListNumberApiController::class, 'updateBlackListNumber']);
    });

    Route::prefix('jobs')->group(function () {
        Route::post('internal/generate-csv', [JobsApiController::class, 'postIndex']);
        Route::post('internal/regenerate-csv', [JobsApiController::class, 'regenerateUnsent']);
    });

    Route::prefix('campaigns')->group(function () {
        Route::post('ignore', [CampaignApiController::class, 'markAsIgnoreFromQueue']);
        Route::post('unignore', [CampaignApiController::class, 'markAsNotIgnoreFromQueue']);
    });

    // batch_files group
    Route::post('batch_files', [BatchFileApiController::class, 'index']);
    Route::post('batch_files/check-status', [BatchFileApiController::class, 'checkStatus']);
    Route::post('batch_files/get-form-content-from-campaign', [BatchFileApiController::class, 'getFormContentFromCampaign']);
});


// group routes that require auth:sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('accounts/', [AccountsApiController::class, 'index']);
    Route::get('accounts/{id}', [AccountsApiController::class, 'show']);
    Route::get('tokens', [AccountsApiController::class, 'showTokens']);

    Route::get('data-source/info', [ContactApiController::class, 'contactsInfo']);
    Route::resource('data-source', ContactApiController::class);

    // ControllerApi methods
    Route::resource('simcards', SimcardApiController::class);
    Route::resource('clients', ClientApiController::class);
    Route::get('mark-processed/{id}', [CampaignApiController::class, 'markAsProcessed'])->name('campaigns.markProcessed');
    Route::get('campaignStats/{id}/stats', [CampaignApiController::class, 'campaignStats']);
    Route::resource('campaigns', CampaignApiController::class);
    Route::resource('recipient_lists', RecipientsListApiController::class);

    Route::post('broadcast_batches', [BroadcastBatchApiController::class, 'store']);
    Route::get('broadcast_batches/{id}', [BroadcastBatchApiController::class, 'show']);
    Route::post('broadcast_batches/mark_as_processed/{id}', [BroadcastBatchApiController::class,'markAsProcessed']);
});


// public routes, without auth
Route::prefix('areas')->name('api.areas.')->group(function () {
    Route::get('get-all-provinces', [AreasApiController::class, 'getAllProvinces']);
    Route::get('get-all-cities', [AreasApiController::class, 'getAllCities']);
    Route::get('cities-by-province/{province}', [AreasApiController::class, 'citiesByProvince']);
});
