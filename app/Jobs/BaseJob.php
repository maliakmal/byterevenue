<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const QUEUE_KEYS = [
        'default',
        'CSV_generate_processing',
        'campaign_contact_processing',
        'import_recipient_list_processing',
        'update_sent_messages_processing',
    ];

    public function tags()
    {
        if (property_exists($this, 'telemetry')) {
            return array_merge(['telemetry'], $this->tags ?? []);
        }
    }
}
