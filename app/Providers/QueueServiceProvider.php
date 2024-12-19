<?php

namespace App\Providers;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Context;
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
        if (config('app.env') !== 'production' || true) {
            Queue::before(function (JobProcessing $event) {
                $event->job->startTime = microtime(true);
                $event->job->startMemory = memory_get_usage();
            });

            Queue::after(function (JobProcessed $event) {
                $startTime     = $event->job->startTime ?? microtime(true);
                $executionTime = microtime(true) - $startTime;

                if (in_array('telemetry', $event->job->payload()['tags'] ?? [], true)) {
                    $jobRawName = explode('\\', $event->job->payload()['displayName']);
                    $jobName    = end($jobRawName);

                    \Log::info('Memory usage for job', [
                        'Job'   => $jobName,
                        'Memory before:' => $event->job->startMemory ?? 'N/A',
                        'Memory after:'  => memory_get_usage(),
                        'Memory used:'   => number_format(memory_get_usage() - ($event->job->startMemory ?? 0)) . ' bytes',
                        'Peak:' => number_format(memory_get_peak_usage() - ($event->job->startMemory ?? 0)) . ' bytes',
                        'Time'  => sprintf('%.6f sec.', $executionTime),
                    ]);
                }
            });
        }
    }
}
