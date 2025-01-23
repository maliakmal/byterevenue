<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\JobsApiController;
use App\Http\Controllers\Api\CampaignApiController;
use App\Http\Controllers\Api\AccountsApiController;
use App\Http\Controllers\Api\ContactApiController;
use App\Http\Controllers\Api\RecipientsListApiController;
use App\Http\Controllers\Api\BroadcastBatchApiController;
use App\Http\Controllers\Api\BroadcastLogApiController;
use App\Http\Controllers\Api\AreasApiController;
use App\Http\Controllers\Api\ShortDomainsApiController;
use App\Http\Controllers\Api\BatchFileApiController;
use App\Http\Controllers\Api\IndicatorsApiController;
use App\Http\Middleware\CheckAdminRole;
use App\Http\Middleware\CheckExternalApiToken;

/// GROUP ROUTES FOR AUTH API
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
/// ########################


/// GROUP ROUTES FOR CUSTOMERS
Route::middleware(['auth:sanctum'])->group(function () {

    /// ### CUSTOMER INDICATORS BLOCK ###
    /// RECIPIENT LISTS INDICATORS
    Route::get('importStatusRecipientListsIndicator', [IndicatorsApiController::class, 'importStatusRecipientLists']);
    Route::get('createdCampaignsChartDataIndicator', [IndicatorsApiController::class, 'createdCampaignsChartData']);
    /// DATA RECORDS INDICATORS
    Route::get('totalContactsIndicator', [IndicatorsApiController::class, 'totalContactsIndicator']);
    Route::get('statusUserListIndicator', [IndicatorsApiController::class, 'statusUserListIndicator']);
    // ACCOUNTS INDICATORS
    Route::get('totalAccountsIndicator', [IndicatorsApiController::class, 'totalAccountsIndicator']);
    Route::get('suspendedAccountsIndicator', [IndicatorsApiController::class, 'suspendedAccountsIndicator']);
    /// TOKENS INDICATORS
    Route::get('tokensGlobalSpentIndicator', [IndicatorsApiController::class, 'tokensGlobalSpentIndicator']);
    /// HISTORY TOKENS INDICATORS
    Route::get('tokensPersonalBalance/{id}', [IndicatorsApiController::class, 'tokensPersonalBalance']);
    Route::get('tokensPersonalSpent/{id}', [IndicatorsApiController::class, 'tokensPersonalSpent']);
    /// ########################

    Route::get('dashboard', [DashboardApiController::class, 'index']);
    Route::get('data-source/info', [ContactApiController::class, 'contactsInfo']);
    Route::resource('data-source', ContactApiController::class);

    Route::get('mark-processed/{id}', [CampaignApiController::class, 'markAsProcessed']);
    Route::apiResource('campaigns', CampaignApiController::class);

    Route::apiResource('recipient_lists', RecipientsListApiController::class);

    /// ???
    Route::post('broadcast_batches', [BroadcastBatchApiController::class, 'store']);
    Route::get('broadcast_batches/{id}', [BroadcastBatchApiController::class, 'show']);
    // Route::post('broadcast_batches/mark_as_processed/{id}', [BroadcastBatchApiController::class, 'markAsProcessed']);
    /// ###

    Route::get('areas/get-all-provinces', [AreasApiController::class, 'getAllProvinces']);
    Route::get('areas/get-all-cities', [AreasApiController::class, 'getAllCities']);
    Route::get('areas/cities-by-province/{province}', [AreasApiController::class, 'citiesByProvince']);

    // Route::post('blacklist-numbers/upload', [BlackListNumberApiController::class, 'updateBlackListNumber']);

    Route::get('tokens/{id}', [AccountsApiController::class, 'showTokens']);
});

/// GROUP ROUTES FOR EXTERNAL API (WEBHOOKS)
Route::middleware([CheckExternalApiToken::class])->group(function () {
    Route::post('test', function () {
        return response()->json(['message' => 'Hello World!']);
    });
    Route::post('messages/update-by-file/sent', [BroadcastLogApiController::class, 'updateSentMessage']);
});

/// ???
Route::post('batch_files', [BatchFileApiController::class, 'index']);
Route::post('batch_files/check-status', [BatchFileApiController::class, 'checkStatus']);
Route::post('batch_files/get-form-content-from-campaign', [BatchFileApiController::class, 'getFormContentFromCampaign']);
/// ###

/// ADMIN options
Route::middleware(['auth:sanctum', CheckAdminRole::class])->group(function () {

    /// ### ADMIN INDICATORS BLOCK ###
    /// GLOBAL QUEUE & SHORT DOMAINS & TOKENS
    Route::get('totalQueueCountsIndicator', [IndicatorsApiController::class, 'totalQueue']);
    Route::get('totalSentOnWeekIndicator', [IndicatorsApiController::class, 'totalSentOnWeek']);
    Route::get('topFiveCampaignsIndicator', [IndicatorsApiController::class, 'topFiveCampaigns']);
    Route::get('topFiveAccountsIndicator', [IndicatorsApiController::class, 'topFiveAccounts']);
    Route::get('topFiveDomainsIndicator', [IndicatorsApiController::class, 'topFiveDomains']);
    Route::get('createdDomainsIndicator', [IndicatorsApiController::class, 'createdDomainsIndicator']);
    Route::get('topFiveAccountsBudget', [IndicatorsApiController::class, 'topFiveAccountsBudget']);
    /// ##############################

    Route::get('accounts/', [AccountsApiController::class, 'index']);
    Route::get('accounts/{id}', [AccountsApiController::class, 'show']);
    Route::delete('accounts/{id}', [AccountsApiController::class, 'delete']);
    Route::post('tokens/change', [AccountsApiController::class, 'storeTokens']);

    Route::get('short-domains', [ShortDomainsApiController::class, 'index']);
    Route::post('short-domains', [ShortDomainsApiController::class, 'store']);
    Route::delete('short-domains/{id}', [ShortDomainsApiController::class, 'destroy']);

    Route::get('jobs/fifo', [JobsApiController::class, 'fifo']);
    Route::get('jobs/campaigns-files', [JobsApiController::class, 'campaignsFiles']);
    Route::get('jobs/clients', [JobsApiController::class, 'clientsFiles']);
    Route::post('jobs/generateCsv', [JobsApiController::class, 'generateCsv']);
    Route::post('jobs/generateCsvByCampaigns', [JobsApiController::class, 'generateCsvByCampaigns']);
    Route::post('jobs/generateCsvByAccounts', [JobsApiController::class, 'generateCsvByAccounts']);
    Route::post('jobs/regenerate', [JobsApiController::class, 'regenerateUnsent']);
    Route::get('jobs/download/{filename}', [JobsApiController::class, 'downloadFile']);

    Route::post('campaigns/ignore', [CampaignApiController::class, 'markAsIgnoreFromQueue']);
    Route::post('campaigns/unignore', [CampaignApiController::class, 'markAsNotIgnoreFromQueue']);
});
