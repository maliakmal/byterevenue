<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SimcardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataFeedController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\RecipientsListController;
use App\Http\Controllers\BroadcastBatchController;
use App\Http\Controllers\JobsController;
use App\Http\Controllers\UrlShortenerController;
use App\Http\Controllers\AccountsController;
use App\Http\Middleware\CheckAdminRole;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::any('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/json-data-feed', [DataFeedController::class, 'getDataFeed'])->name('json_data_feed');
    Route::get('/tokens', [AccountsController::class, 'tokens'])->name('accounts.tokens');

    Route::resource('data-source', ContactController::class);
    Route::resource('simcards', SimcardController::class);
    Route::resource('clients', ClientController::class);
    Route::resource('campaigns', CampaignController::class);
    Route::resource('recipient_lists', RecipientsListController::class);
    Route::resource('broadcast_batches', BroadcastBatchController::class);
    Route::resource('accounts', AccountsController::class);
    Route::get('/mark-processed/{id}', [CampaignController::class, 'markAsProcessed'])->name('campaigns.markProcessed');
    Route::get('/user', [\App\Http\Controllers\Api\BlackListNumberController::class, 'updateBlackListNumber']);

    Route::get('/mark-processed/{id}', [CampaignController::class, 'markAsProcessed'])->name('campaigns.markProcessed');
    Route::get('black-list-numbers/user', [\App\Http\Controllers\BlackListNumberController::class, 'getBlackListNumberForUser'])->name('block_numbers_user');
    Route::get('/introductory/disable', [\App\Http\Controllers\DashboardController::class, 'disableIntroductory'])->name('block_numbers_user');
});

Route::middleware([CheckAdminRole::class])->group(function () {

    Route::get('/jobs/fifo', [JobsController::class, 'index'])->name('jobs.index');
    Route::post('/jobs/regenerate', [JobsController::class, 'regenerateUnsent'])->name('jobs.regenerate');
    Route::get('/jobs/campaigns', [JobsController::class, 'campaigns'])->name('jobs.campaigns');
    Route::post('/jobs/campaigns/regenerate', [JobsController::class, 'regenerateUnsent'])->name('jobs.regenerate');
    Route::get('/download/{filename}', [JobsController::class, 'downloadFile'])->name('download.file');
    Route::post('/jobs', [JobsController::class, 'index'])->name('jobs.postIndex');
    Route::post('/accounts/store-tokens', [AccountsController::class, 'storeTokens'])->name('accounts.storeTokens');
    Route::resource('url_shorteners', UrlShortenerController::class);
    Route::prefix('settings')->group(function (){
        Route::prefix('upload-messages')->group(function (){
            Route::get('/', [\App\Http\Controllers\SettingController::class, 'uploadSendDataIndex'])->name('messages.uploadMessageSendDataIndex');
            Route::post('/', [\App\Http\Controllers\SettingController::class, 'uploadSendData'])->name('messages.uploadMessageSendData');
        });
        Route::prefix('upload-black-numbers')->group(function (){
            Route::get('/', [\App\Http\Controllers\SettingController::class, 'uploadBlackListNumberIndex'])->name('messages.uploadBlackListNumberIndex');
            Route::post('/', [\App\Http\Controllers\SettingController::class, 'uploadBlackListNumber'])->name('messages.uploadBlackListNumber');
        });
    });
    Route::resource('settings', \App\Http\Controllers\SettingController::class);
    Route::resource('black-list-numbers', \App\Http\Controllers\BlackListNumberController::class);
    Route::resource('black-list-words', \App\Http\Controllers\BlackListWordController::class);
    Route::prefix('reports')->group(function (){
        Route::get('messages', [\App\Http\Controllers\ReportController::class, 'messages'])->name('reports.messages');
        Route::get('campaigns', [\App\Http\Controllers\ReportController::class, 'campaigns'])->name('reports.campaigns');
    });
    Route::get('/user/campaigns', [CampaignController::class, 'getCampaignForUser']);
});

Route::get('/forbidden', function () {
    return view('404');
})->name('forbidden');
