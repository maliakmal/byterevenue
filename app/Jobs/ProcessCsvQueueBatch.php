<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\BroadcastLog;
use App\Models\Message;
use App\Models\Campaign;
use App\Models\CampaignShortUrl;
use App\Services\Campaign\CampaignService;
use App\Repositories\Model\CampaignShortUrl\CampaignShortUrlRepository;
use App\Repositories\Contract\UrlShortener\UrlShortenerRepositoryInterface;
use Illuminate\Support\Facades\Log;

class ProcessCsvQueueBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 1;

    protected $offset        = 0;
    protected $batchSize     = 100;
    protected $url_shortener = null;
    protected $logs          = null;
    protected $batch_no      = null;
    protected $batch_file    = null;
    protected $is_last       = false;
    protected $type          = 'fifo';
    protected $campaign_ids  = null;
    protected $message_id    = null;
    protected $campaign_short_urls = [];
    protected $campaignShortUrlRepository = null;
    protected $urlShortenerRepository     = null;
    protected $broadcastLogRepository     = null;

    const QUEUE_KEY = 'CSV_generate_processing';

    /**
     * Create a new job instance.
     */
    public function __construct($params = [])
    {
        $this->campaignShortUrlRepository = new CampaignShortUrlRepository(new CampaignShortUrl());
        $this->urlShortenerRepository     = app()->make(UrlShortenerRepositoryInterface::class);
        $this->url_shortener = $params['url_shortener'] ?? $this->url_shortener;
        $this->batch_no      = $params['batch_no']      ?? $this->batch_no;
        $this->type          = $params['type']          ?? $this->type;
        $this->campaign_ids  = $params['campaign_ids']  ?? $this->campaign_ids;
        $this->message_id    = $params['message_id']    ?? $this->message_id;
        $this->offset        = $params['offset']        ?? $this->offset;
        $this->batchSize     = $params['batchSize']     ?? $this->batchSize;
        $this->batch_file    = $params['batch_file']    ?? $this->batch_file;
        $this->is_last       = $params['is_last']       ?? $this->is_last;
        $this->campaign_short_urls = $params['campaign_short_urls'] ?? $this->campaign_short_urls;

        $this->onQueue(self::QUEUE_KEY);
    }

    /**
     * Execute the job.
     */
    public function handle(CampaignService $campaign_service): void
    {
        $batch_no = $this->batch_no;
        $ignored_campaigns = Campaign::select('id')->where('is_ignored_on_queue', true)->get()->pluck('id');

        $query = BroadcastLog::query()
            ->with(['campaign', 'message'])
            ->whereNotIn('campaign_id', $ignored_campaigns)
            ->whereNull('batch')
            ->limit($this->batchSize);

        if ('campaign' === $this->type && !empty($this->campaign_ids)) {
            $query->where('campaign_id', $this->campaign_ids);
        }

        $this->logs = $query->get();

        if ($this->logs->isEmpty()) {
            Log::debug('No matching entries found - skipping...');
            $this->batch_file->update([
                'is_ready' => 1,
                'has_errors' => 1
            ]);

            return;
        }

        Log::info('ProcessCsvQueueBatchJob -> logs count: ' . count($this->logs) . ' Batch no: ' . $this->batch_no . ' Offset: ' . $this->offset);

        $ids = [];
        $cases = '';
        $casesCount = 0;
        $campaign_short_url_map = $this->campaign_short_urls;

        foreach ($this->logs as $log) {
            $ids[] = "'". $log->id ."'";
            $campaign = $log->campaign;
            $message = $log->message;

            if ($this->message_id) {
                Log::debug('ProcessCsvQueueBatchJob -> request of message: ' . $this->message_id);
                $message = Message::find($this->message_id);
            }

            if (!$message) {
                Log::error('ProcessCsvQueueBatchJob -> Message not found for log id - ' . $log->id . ' - skipping...');

                continue;
            }

            $campaign_short_url = isset($campaign_short_url_map[$campaign->id]) ? $campaign_short_url_map[$campaign->id] : null;

            if (!$campaign_short_url) {
                Log::debug('ProcessCsvQueueBatchJob -> campaign_short_url doesnt exist for log id ' . $log->id . ' - skipping...', [
                    'log' => $log,
                    'campaign' => $campaign,
                ]);

                continue;
            }

            $generated_url = $campaign_service->generateUrlForCampaignFromAlias($campaign_short_url->url_shortener, $log->slug);
            $message_body = $message->getParsedMessage($generated_url);
            $cases .= "WHEN '{$log->id}' THEN '" . addslashes($message_body) . "'";
            $casesCount++;
        }

        if ($casesCount == 0) {
            Log::error('ProcessCsvQueueBatchJob -> Cases count to create is 0 - skipping...');
            $this->batch_file->update([
                'is_ready' => 1,
                'has_errors' => 1.
            ]);

            return;
        }

        $idList = implode(",", $ids);

        try {
            $sql = "UPDATE `broadcast_logs`
            SET `message_body` = CASE `id`
                {$cases}
            END,
            " . ($this->message_id ? " `message_id` = '$this->message_id', " : "") . "
            `is_downloaded_as_csv` = 1,
            `batch` = '$batch_no',
            `updated_at` = NOW()
            WHERE `id` IN ({$idList})";

            \DB::statement($sql);

            $this->batch_file->increment('generated_count', $casesCount);

            if ($this->is_last == true) {
                $this->batch_file->update(['is_ready' => 1]);
            }
        } catch (\Exception $e) {
            Log::error('ProcessCsvQueueBatchJob -> Error updating broadcast_logs: ' . $e->getMessage());
        }
    }
}
