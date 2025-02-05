<?php

use App\Http\Controllers\Web\AccountsController;
use App\Http\Controllers\Web\BroadcastBatchController;
use App\Http\Controllers\Web\CampaignController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DataFeedController;
use App\Http\Controllers\Web\JobsController;
use App\Http\Controllers\Web\RecipientsListController;
use App\Http\Controllers\Web\SimcardController;
use App\Http\Controllers\Web\UrlShortenerController;
use App\Http\Controllers\Web\SettingController;
use App\Http\Controllers\Web\BlackListNumberController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\BlackListWordController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\AreasController;
use App\Http\Controllers\Web\UpdateSentMessagesController;
use App\Http\Controllers\Web\AliasesController;
use App\Http\Middleware\CheckAdminRole;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::view('api-docs', 'repidoc_api');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::any('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('introductory/disable', [DashboardController::class, 'disableIntroductory'])->name('block_numbers_user');

    Route::get('json-data-feed', [DataFeedController::class, 'getDataFeed'])->name('json_data_feed');
    Route::get('tokens', [AccountsController::class, 'tokens'])->name('accounts.tokens');

    Route::get('data-source/info', [ContactController::class, 'contactsInfo']);
    Route::resource('data-source', ContactController::class);

    Route::resource('simcards', SimcardController::class);
    Route::resource('clients', ClientController::class);

    Route::get('mark-processed/{id}', [CampaignController::class, 'markAsProcessed'])->name('campaigns.markProcessed');
    Route::resource('campaigns', CampaignController::class);

    Route::resource('recipient_lists', RecipientsListController::class);
    Route::resource('broadcast_batches', BroadcastBatchController::class);
    Route::resource('accounts', AccountsController::class);

    Route::get('user', [BlackListNumberController::class, 'updateBlackListNumber']);
    Route::get('black-list-numbers/user', [BlackListNumberController::class, 'getBlackListNumberForUser'])->name('block_numbers_user');

    Route::get('areas/get-all-provinces', [AreasController::class, 'getAllProvinces']);
    Route::get('areas/get-all-cities', [AreasController::class, 'getAllCities']);
    Route::get('areas/cities-by-province/{province}', [AreasController::class, 'citiesByProvince']);

    Route::middleware([CheckAdminRole::class])->group(function () {
        Route::get('jobs/fifo', [JobsController::class, 'index'])->name('jobs.index');
        Route::post('jobs/generateCsv', [JobsController::class, 'generateCsv'])->name('jobs.generateCsv');
        Route::post('jobs/generateCsvByCampaigns', [JobsController::class, 'generateCsvByCampaigns'])->name('jobs.generateCsvByCampaigns');
        Route::post('jobs/regenerate', [JobsController::class, 'regenerateUnsent'])->name('jobs.regenerate');
        Route::get('jobs/campaigns', [JobsController::class, 'campaigns'])->name('jobs.campaigns');
        Route::get('download/{id}', [JobsController::class, 'downloadFile'])->name('download.file');


        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::post('/roles/{role}/permissions', [RoleController::class, 'assignPermissions'])->name('roles.assignPermissions');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    
        // User management routes
        Route::post('/accounts/{user}/roles', [AccountsController::class, 'assignRole'])->name('accounts.assignRole');
    
        Route::post('accounts/store-tokens', [AccountsController::class, 'storeTokens'])->name('accounts.storeTokens');
        Route::resource('url_shorteners', UrlShortenerController::class);

        Route::get('settings/upload-messages', [SettingController::class, 'uploadSendDataIndex'])->name('messages.uploadMessageSendDataIndex');
        Route::post('settings/upload-messages', [SettingController::class, 'uploadSendData'])->name('messages.uploadMessageSendData');
        Route::get('settings/upload-black-numbers', [SettingController::class, 'uploadBlackListNumberIndex'])->name('messages.uploadBlackListNumberIndex');
        Route::post('settings/upload-black-numbers', [SettingController::class, 'uploadBlackListNumber'])->name('messages.uploadBlackListNumber');
        Route::resource('settings', SettingController::class);

        Route::resource('black-list-numbers', BlackListNumberController::class);
        Route::resource('black-list-words', BlackListWordController::class);

        Route::get('reports/messages', [ReportController::class, 'messages'])->name('reports.messages');
        Route::get('reports/campaigns', [ReportController::class, 'campaigns'])->name('reports.campaigns');
        Route::get('user/campaigns', [CampaignController::class, 'getCampaignForUser']);

        // additional info
        Route::get('update-sent-messages', [UpdateSentMessagesController::class, 'index'])->name('update_sent_messages.index');
        Route::get('download_updates_file/{id}', [UpdateSentMessagesController::class, 'download'])->name('download_updates_file');
        Route::get('aliases', [AliasesController::class, 'index'])->name('aliases.index');
        Route::get('aliases/{id}/refresh', [AliasesController::class, 'refresh']);
    });

    // Socket test route
    Route::get('socket-test', function () {
//        broadcast(new \App\Events\PublicEvent('test_message'));
//        broadcast(new \App\Events\PrivateEvent('private_test_message', request()->user()));
        return view('socket-test');
    });

    // clear cache
    Route::get('clear-cache', function () {
        \Artisan::call('optimize:clear');
        return redirect()->back();
    });
});
