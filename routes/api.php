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
use App\Http\Controllers\Api\ShortDomainsApiController;

// group auth routes for auth api
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


// group routes that require auth:sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    // ADMIN options!
    Route::get('accounts/', [AccountsApiController::class, 'index']);
    Route::get('accounts/{id}', [AccountsApiController::class, 'show']);
    Route::get('tokens/{id}', [AccountsApiController::class, 'showTokens']);
    Route::post('tokens/change', [AccountsApiController::class, 'storeTokens']);
    // ####################

    Route::get('data-source/info', [ContactApiController::class, 'contactsInfo']);
    Route::resource('data-source', ContactApiController::class);

    Route::apiResource('simcards', SimcardApiController::class);
    Route::apiResource('clients', ClientApiController::class);
    Route::get('mark-processed/{id}', [CampaignApiController::class, 'markAsProcessed']);
    Route::get('campaignStats/{id}/stats', [CampaignApiController::class, 'campaignStats']);
    Route::apiResource('campaigns', CampaignApiController::class);
    Route::apiResource('recipient_lists', RecipientsListApiController::class);

    Route::post('broadcast_batches', [BroadcastBatchApiController::class, 'store']);
    Route::get('broadcast_batches/{id}', [BroadcastBatchApiController::class, 'show']);
    Route::post('broadcast_batches/mark_as_processed/{id}', [BroadcastBatchApiController::class, 'markAsProcessed']);

    // ADMIN options!
    Route::prefix('short-domains')->controller(ShortDomainsApiController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::delete('/{id}', 'destroy');
    });

    Route::prefix('jobs')->controller(JobsApiController::class)->group(function () {
        Route::get('fifo', [JobsApiController::class, 'fifo'])->name('jobs.fifo');
        Route::get('campaigns', [JobsApiController::class, 'campaigns'])->name('jobs.campaigns');
        Route::post('/', [JobsApiController::class, 'postIndex'])->name('jobs.postIndex');
        Route::post('regenerate', [JobsApiController::class, 'regenerateUnsent'])->name('jobs.regenerate');
        Route::get('/download/{id}',[JobsApiController::class, 'downloadFile'])->name('jobs.download-file');
    });
    // ####################
});

Route::prefix('areas')->name('api.areas.')->group(function () {
    Route::get('get-all-provinces', [AreasApiController::class, 'getAllProvinces']);
    Route::get('get-all-cities', [AreasApiController::class, 'getAllCities']);
    Route::get('cities-by-province/{province}', [AreasApiController::class, 'citiesByProvince']);
});

Route::prefix('messages')->group(function () {
    Route::post('update-by-file/sent', [BroadcastLogApiController::class, 'updateSentMessage']);
    Route::post('update/sent', [JobsApiController::class, 'updateSentMessage']);
    Route::post('update/clicked', [JobsApiController::class, 'updateClickMessage']);
});

Route::prefix('blacklist-numbers')->group(function () {
    Route::post('upload', [BlackListNumberApiController::class, 'updateBlackListNumber']);
});

Route::prefix('campaigns')->group(function () {
    Route::post('ignore', [CampaignApiController::class, 'markAsIgnoreFromQueue']);
    Route::post('unignore', [CampaignApiController::class, 'markAsNotIgnoreFromQueue']);
});

Route::post('batch_files', [BatchFileApiController::class, 'index']);
Route::post('batch_files/check-status', [BatchFileApiController::class, 'checkStatus']);
Route::post('batch_files/get-form-content-from-campaign', [BatchFileApiController::class, 'getFormContentFromCampaign']);
