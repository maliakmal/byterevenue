<?php

namespace App\Jobs;

use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Models\UpdateSentMessage;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Trait\CSVReader;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;

class UpdateSentMessagesJob extends BaseJob implements ShouldQueue
{
    public $timeout = 600; // 10 minutes
    public $tries = 1;

    const QUEUE_KEY = 'update_sent_messages_processing';

    use CSVReader;

    protected $broadcastLogRepository;
    protected $file_id;

    /**
     * Create a new job instance.
     */
    public function __construct(string $file_id)
    {
        $this->broadcastLogRepository = app(BroadcastLogRepositoryInterface::class);

        $this->file_id = $file_id;

        $this->onQueue(self::QUEUE_KEY);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info('Start processing file: ' . $this->file_id);

        $file = UpdateSentMessage::find($this->file_id);

        if (!$file) {
            \Log::error('File not found: ' . $this->file_id);

            return;
        }

        $file->update(['status' => UpdateSentMessage::STATUS_PROCESSING]);

        $content = Storage::disk('local')->get('update_sent_messages/' . $file->file_name);
        $csv = $this->csvToCollection($content);
        $message_slugs = $csv->pluck('UID')->toArray();
\Log::debug('$message_slugs: ', ['val' => $message_slugs]);
        $file->update(['total_rows' => count($message_slugs)]);

        $number_of_updated_rows = BroadcastLog::query()
            ->whereIn('slug', $message_slugs)
            ->where('is_sent', 0)
            ->where('sent_at', null)
            ->whereHas('campaign', function ($query) {
                $query->whereIn('status', Campaign::STATUS_PROCESSING, Campaign::STATUS_DONE);
            })
            ->update([
                'sent_at' => now()->toDateTimeString(),
                'is_sent' => 1,
            ]);

        $file->update([
            'status' => UpdateSentMessage::STATUS_COMPLETED,
            'processed_rows' => $number_of_updated_rows,
        ]);
    }
}
