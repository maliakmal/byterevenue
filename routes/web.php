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

use Filament\Http\Middleware\Authenticate;


Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/json-data-feed', [DataFeedController::class, 'getDataFeed'])->name('json_data_feed');
    Route::get('/tokens', [AccountsController::class, 'tokens'])->name('accounts.tokens');

    Route::resource('contacts', ContactController::class);
    Route::resource('simcards', SimcardController::class);
    Route::resource('clients', ClientController::class);
    Route::resource('campaigns', CampaignController::class);
    Route::resource('recipient_lists', RecipientsListController::class);
    Route::resource('broadcast_batches', BroadcastBatchController::class);
    Route::resource('accounts', AccountsController::class);
    Route::get('/mark-processed/{id}', [CampaignController::class, 'markAsProcessed'])->name('campaigns.markProcessed');

    

    
});
Route::middleware([CheckAdminRole::class])->group(function () {

    Route::get('/jobs', [JobsController::class, 'index'])->name('jobs.index');
    Route::get('/download/{filename}', [JobsController::class, 'downloadFile'])->name('download.file');
    Route::post('/jobs', [JobsController::class, 'index'])->name('jobs.postIndex');
    Route::post('/accounts/store-tokens', [AccountsController::class, 'storeTokens'])->name('accounts.storeTokens');
    Route::resource('url_shorteners', UrlShortenerController::class);
});

Route::get('/forbidden', function () {
    return view('404');
})->name('forbidden');
