<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataFeedController;
use App\Http\Controllers\UrlShortenerController;
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
        Route::post('/generate-csv', [\App\Http\Controllers\JobsController::class, 'index']);
        Route::post('/regenerate-csv', [\App\Http\Controllers\JobsController::class, 'regenerateUnsent']);
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
    Route::get('/json-data-feed', [DataFeedController::class, 'getDataFeedApi']);

    Route::controller(AccountsController::class)->group(function () {
        Route::prefix('accounts')->group(function () {
            Route::get('/', 'indexApi');
            Route::post('/store-tokens', 'storeTokensApi');
            Route::get('/{id}', 'showApi');
        });
        Route::get('/tokens', 'showTokensApi');
    });

    Route::controller(UrlShortenerController::class)->prefix('url-shorteners')->group(function () {
        Route::get('/', 'indexApi');
        Route::post('/', 'storeApi');
        Route::put('/{id}', 'updateApi');
        Route::delete('/{id}', 'deleteApi');
    });

});

// public routes
Route::prefix('areas')->name('api.areas.')->group(function () {
    Route::get('get-all-provinces', [\App\Http\Controllers\Api\AreasController::class, 'getAllProvinces']);
    Route::get('get-all-cities', [\App\Http\Controllers\Api\AreasController::class, 'getAllCities']);
    Route::get('cities-by-province/{province}', [\App\Http\Controllers\Api\AreasController::class, 'citiesByProvince']);
});
