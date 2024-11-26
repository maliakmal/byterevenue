<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

$check_propagation_schedule = config('app.domain.schedule_check_propagation');
Schedule::job(new \App\Jobs\CheckDomainPropagationJob())->$check_propagation_schedule();

$check_update_click_schedule = config('setting.schedule_update_click', 'everyTenMinutes');
Schedule::command('keitaro:update-clicks')->$check_update_click_schedule()->withoutOverlapping();

$collect_logs_schedule = config('settings.storage.archive_logs.period', 'hourly');
Schedule::command('storage:collect-logs')->$collect_logs_schedule()->withoutOverlapping();

Schedule::command('auth:clear-resets')->everyFifteenMinutes();

// Schedule::command('sync:recipients-group')->everyHour()->withoutOverlapping();
