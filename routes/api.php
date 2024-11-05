<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\BlackListNumberController;
use App\Http\Controllers\BlackListWordController;
use App\Http\Controllers\BroadcastBatchController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataFeedController;
use App\Http\Controllers\RecipientsListController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SimcardController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UrlShortenerController;
use App\Http\Middleware\CheckAdminRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobsController;

// User data for livewire
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// group auth routes for api
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
Route::post('/forgot-password', [\App\Http\Controllers\Api\AuthController::class, 'forgotPassword']);
Route::get('/reset-password/{token}', [\App\Http\Controllers\Api\AuthController::class, 'resetPassword']);
Route::post('/refresh', [\App\Http\Controllers\Api\AuthController::class, 'refresh']);
Route::get('/me', [\App\Http\Controllers\Api\AuthController::class, 'me'])->middleware('auth:sanctum');

// group routes that require external api token (webhooks)
Route::middleware([/*\App\Http\Middleware\CheckExternalApiToken::class, */])->group(function () {
    Route::prefix('messages')->group(function () {
        Route::post('/update-by-file/sent', [\App\Http\Controllers\Api\BroadcastLogController::class, 'updateSentMessage']);
        Route::post('/update/sent', [JobsController::class, 'updateSentMessage']);
        Route::post('/update/clicked', [JobsController::class, 'updateClickMessage']);
    });

    Route::prefix('blacklist-numbers')->group(function () {
        Route::post('/upload', [\App\Http\Controllers\Api\BlackListNumberController::class, 'updateBlackListNumber']);
    });
    Route::prefix('jobs')->group(function () {
        Route::post('/generate-csv', [JobsController::class, 'index']);
        Route::post('/regenerate-csv', [JobsController::class, 'regenerateUnsent']);
    });
    Route::prefix('campaigns')->group(function () {
        Route::post('/ignore', [\App\Http\Controllers\Api\CampaignController::class, 'markAsIgnoreFromQueue']);
        Route::post('/unignore', [\App\Http\Controllers\Api\CampaignController::class, 'markAsNotIgnoreFromQueue']);
    });

    Route::prefix('batch_files')->group(function () {
        Route::post('/', [\App\Http\Controllers\Api\BatchFileController::class, 'index']);
        Route::post('/check-status', [\App\Http\Controllers\Api\BatchFileController::class, 'checkStatus']);
        Route::post('/get-form-content-from-campaign', [\App\Http\Controllers\Api\BatchFileController::class, 'getFormContentFromCampaign']);
    });
});

// group routes that require auth:sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    Route::any('/dashboard', [DashboardController::class, 'indexApi']);
    Route::get('/introductory/disable', [DashboardController::class, 'disableIntroductoryApi']);
    Route::get('/json-data-feed', [DataFeedController::class, 'getDataFeedApi']);

    Route::controller(AccountsController::class)->group(function () {
        Route::prefix('accounts')->group(function () {
            Route::get('/', 'indexApi');
            Route::post('/store-tokens', 'storeTokensApi');
            Route::get('/{id}', 'showApi');
        });
        Route::get('/tokens', 'showTokensApi');
    });

    Route::middleware([CheckAdminRole::class])->group(function () {
        Route::controller(UrlShortenerController::class)->prefix('url-shorteners')->group(function () {
            Route::get('/', 'indexApi');
            Route::post('/', 'storeApi');
            Route::post('/{id}', 'updateApi');
            Route::delete('/{id}', 'deleteApi');
        });

        Route::controller(SettingController::class)->prefix('settings')->group(function () {
            Route::get('/', 'indexApi');
            Route::post('/', 'storeApi');
            Route::post('/{id}', 'updateApi');
            Route::delete('/{id}', 'deleteApi');
        });
    });

    Route::prefix('data-source')->group(function (){
        Route::get('/', [ContactController::class, 'indexApi']);
        Route::get('/{id}', [ContactController::class, 'showApi']);
        Route::get('/{id}/edit', [ContactController::class, 'editApi']);
        Route::post('/', [ContactController::class, 'storeApi']);
        Route::put('/{id}', [ContactController::class, 'updateApi']);
        Route::delete('/{id}', [ContactController::class, 'destroyApi']);
    });

    Route::controller(SimcardController::class)->prefix('simcards')->group(function () {
        Route::get('/', 'indexApi');
        Route::post('/', 'storeApi');
        Route::get('/{id}', 'showApi');
        Route::put('/{id}', 'updateApi');
        Route::delete('/{id}', 'destroyApi');
    });

    Route::controller(ClientController::class)->prefix('clients')->group(function () {
        Route::get('/', 'indexApi');
        Route::post('/', 'storeApi');
        Route::get('/{id}', 'showApi');
        Route::put('/{id}', 'updateApi');
        Route::delete('/{id}', 'destroyApi');
    });

    Route::controller(CampaignController::class)->prefix('campaigns')->group(function () {
        Route::get('/', 'indexApi');
        Route::post('/', 'storeApi');
        Route::get('/{id}', 'showApi');
        Route::put('/{id}', 'updateApi');
        Route::delete('/{id}', 'destroyApi');
        Route::post('/mark-processed/{id}', 'markAsProcessedApi');
    });

    Route::controller(RecipientsListController::class)->prefix('recipient_lists')->group(function () {
        Route::get('/', 'indexApi');
        Route::post('/', 'storeApi');
        Route::get('/{id}', 'showApi');
        Route::put('/{id}', 'updateApi');
        Route::delete('/{id}', 'destroyApi');
    });

    Route::controller(BroadcastBatchController::class)->prefix('broadcast_batches')->group(function () {
        Route::post('/', 'storeApi');
        Route::get('/{id}', 'showApi');
        Route::post('mark_as_processed/{id}', 'markAsProcessedApi');
    });

    Route::get('black-list-numbers/user', [BlackListNumberController::class, 'getBlackListNumberForUserApi']);

    Route::middleware([CheckAdminRole::class])->group(function () {
        Route::controller(BlackListNumberController::class)->prefix('black-list-numbers')->group(function () {
            Route::get('/user', 'getBlackListNumberForUserApi');
            Route::get('/', 'indexApi');
            Route::post('/', 'storeApi');
            Route::put('/{id}', 'updateApi');
            Route::delete('/{id}', 'destroyApi');
        });

        Route::controller(BlackListWordController::class)->prefix('black-list-words')->group(function () {
            Route::get('/', 'indexApi');
            Route::post('/', 'storeApi');
            Route::get('/{id}', 'show');
            Route::put('/{id}', 'updateApi');
            Route::delete('/{id}', 'destroyApi');
        });

        Route::controller(SettingController::class)->prefix('settings')->group(function () {
            Route::post('/upload-messages', 'uploadSendDataApi');
            Route::post('/upload-black-numbers', 'uploadBlackListNumberApi');
            Route::get('/', 'indexApi');
            Route::post('/', 'storeApi');
            Route::post('/{id}', 'updateApi');
            Route::delete('/{id}', 'deleteApi');
        });

        Route::controller(JobsController::class)->prefix('jobs')->group(function () {
            Route::get('/fifo', 'indexApi');
            Route::post('/', 'createJobApi');
            Route::post('/regenerate', 'regenerateUnsentApi');
            Route::post('/campaigns/regenerate', 'regenerateUnsentApi');
            Route::get('/campaigns', 'campaignsApi');
        });

        Route::controller(ReportController::class)->prefix('reports')->group(function (){
            Route::get('messages', 'messagesApi');
            Route::get('messages/csv','messagesCSVApi');
            Route::get('campaigns', 'campaignsApi');
            Route::get('campaigns/csv','campaignsCSVApi');
        });

        Route::get('/user/campaigns', [CampaignController::class, 'getCampaignsForUserApi']);
    });
});

// public routes
Route::prefix('areas')->name('api.areas.')->group(function () {
    Route::get('get-all-provinces', [\App\Http\Controllers\Api\AreasController::class, 'getAllProvinces']);
    Route::get('get-all-cities', [\App\Http\Controllers\Api\AreasController::class, 'getAllCities']);
    Route::get('cities-by-province/{province}', [\App\Http\Controllers\Api\AreasController::class, 'citiesByProvince']);
});
