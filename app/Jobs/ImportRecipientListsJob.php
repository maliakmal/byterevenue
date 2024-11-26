<?php

namespace App\Jobs;

use App\Models\ImportRecipientsList;
use App\Services\RecipientList\RecipientListService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class ImportRecipientListsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ImportRecipientsList $list;
    private RecipientListService $recipient_list_service;

    public $timeout = 600; // 10 minutes
    public $tries = 1;

    const QUEUE_KEY = 'import_recipient_list_processing';

    /**
     * Create a new job instance.
     */
    public function __construct(ImportRecipientsList $importRecipientsList)
    {
        $this->list = $importRecipientsList;
        $this->recipient_list_service = new RecipientListService;
        $this->onQueue(self::QUEUE_KEY);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tempPath = Storage::path($this->list->file_path);
        $data     = $this->list->data;
        $file     = new File($tempPath);

        $this->recipient_list_service->store($data, $file, $this->list->user);
        $this->list->update(['processed_at' => now()->toDateTimeString()]);
        \Log::info('ImportRecipientListsJob: ' . $this->list->id . ' processed');

        FillingRecipientGroupJob::dispatch();
        \Log::info('FillingRecipientGroupJob dispatched');
    }

    /**
     * The job failed to process.
     */
    public function failed(): void
    {
        $this->list->update([
            'processed_at' => now()->toDateTimeString(),
            'is_failed'    => true,
        ]);

        \Log::info('ImportRecipientListsJob: ' . $this->list->id . ' failed');
    }
}
