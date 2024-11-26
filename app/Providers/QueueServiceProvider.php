<?php

namespace App\Providers;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class QueueServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        if (config('app.env') !== 'production') {
            Queue::before(function (JobProcessing $event) {
                $event->job->startTime = microtime(true);
                $event->job->startMemory = memory_get_usage();
            });

            Queue::after(function (JobProcessed $event) {
                $startTime     = $event->job->startTime ?? microtime(true);
                $executionTime = microtime(true) - $startTime;

                \Log::debug('Memory usage for job', [
                    'Memory after:'  => $event->job->startMemory ?? 'N/A',
                    'Memory before:' => memory_get_usage(),
                    'Peak:' => memory_get_peak_usage(),
                    'Time'  => sprintf('%.6f sec.', $executionTime),
                ]);
            });
        }
    }
}
