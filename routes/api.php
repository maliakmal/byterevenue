<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\BroadcastLogApiController;
use App\Http\Controllers\Api\JobsApiController;
use App\Http\Controllers\Api\CampaignApiController;
use App\Http\Controllers\Api\AccountsApiController;
use App\Http\Controllers\Api\ContactApiController;
use App\Http\Controllers\Api\RecipientsListApiController;
use App\Http\Controllers\Api\BroadcastBatchApiController;
use App\Http\Controllers\Api\AreasApiController;
use App\Http\Controllers\Api\ShortDomainsApiController;
use App\Http\Controllers\Api\BatchFileApiController;
use App\Http\Middleware\CheckAdminRole;

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
    Route::get('data-source/info', [ContactApiController::class, 'contactsInfo']);
    Route::resource('data-source', ContactApiController::class);

    Route::get('mark-processed/{id}', [CampaignApiController::class, 'markAsProcessed']);
//    Route::get('campaignStats/{id}/stats', [CampaignApiController::class, 'campaignStats']);
    Route::apiResource('campaigns', CampaignApiController::class);

    Route::apiResource('recipient_lists', RecipientsListApiController::class);

    // ???
    Route::post('broadcast_batches', [BroadcastBatchApiController::class, 'store']);
    Route::get('broadcast_batches/{id}', [BroadcastBatchApiController::class, 'show']);
    Route::post('broadcast_batches/mark_as_processed/{id}', [BroadcastBatchApiController::class, 'markAsProcessed']);
    // ###

    Route::get('areas/get-all-provinces', [AreasApiController::class, 'getAllProvinces']);
    Route::get('areas/get-all-cities', [AreasApiController::class, 'getAllCities']);
    Route::get('areas/cities-by-province/{province}', [AreasApiController::class, 'citiesByProvince']);

//    Route::post('messages/update-by-file/sent', [BroadcastLogApiController::class, 'updateSentMessage']);

//    Route::post('blacklist-numbers/upload', [BlackListNumberApiController::class, 'updateBlackListNumber']);
});

Route::post('batch_files', [BatchFileApiController::class, 'index']);
Route::post('batch_files/check-status', [BatchFileApiController::class, 'checkStatus']);
Route::post('batch_files/get-form-content-from-campaign', [BatchFileApiController::class, 'getFormContentFromCampaign']);

// ADMIN options
Route::middleware(['auth:sanctum', CheckAdminRole::class])->group(function () {
    Route::get('accounts/', [AccountsApiController::class, 'index']);
    Route::get('accounts/{id}', [AccountsApiController::class, 'show']);
    Route::delete('accounts/{id}', [AccountsApiController::class, 'delete']);
    Route::get('tokens/{id}', [AccountsApiController::class, 'showTokens']);
    Route::post('tokens/change', [AccountsApiController::class, 'storeTokens']);

    Route::get('short-domains', [ShortDomainsApiController::class, 'index']);
    Route::post('short-domains', [ShortDomainsApiController::class, 'store']);
    Route::delete('short-domains/{id}', [ShortDomainsApiController::class, 'destroy']);

    Route::get('jobs/fifo', [JobsApiController::class, 'fifo']);
    Route::get('/jobs/clients', [JobsApiController::class, 'getQueueStats']);
    Route::post('jobs/generateCsv', [JobsApiController::class, 'generateCsv']);
    Route::post('jobs/generateCsvByCampaigns', [JobsApiController::class, 'generateCsvByCampaigns']);
    Route::post('jobs/regenerate', [JobsApiController::class, 'regenerateUnsent']);
    Route::get('jobs/download/{filename}', [JobsApiController::class, 'downloadFile']);

    Route::post('campaigns/ignore', [CampaignApiController::class, 'markAsIgnoreFromQueue']);
    Route::post('campaigns/unignore', [CampaignApiController::class, 'markAsNotIgnoreFromQueue']);
});
