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

    Route::resource('contacts', ContactController::class);
    Route::resource('simcards', SimcardController::class);
    Route::resource('clients', ClientController::class);
    Route::resource('campaigns', CampaignController::class);
    Route::resource('recipient_lists', RecipientsListController::class);
    Route::resource('broadcast_batches', BroadcastBatchController::class);
});

