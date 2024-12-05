<?php

namespace App\Jobs;

use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\BroadcastLog;
use App\Models\CampaignShortUrl;
use App\Models\Campaign;
use App\Models\UrlShortener;
use App\Models\Message;
use App\Services\Campaign\CampaignService;
use App\Repositories\Model\CampaignShortUrl\CampaignShortUrlRepository;
use App\Repositories\Contract\UrlShortener\UrlShortenerRepositoryInterface;
use Illuminate\Support\Facades\Log;

class ProcessCsvRegenQueueBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 1;

    protected $offset            = 0;
    protected $batchSize         = 1000;
    protected $url_shortener     = null;
    protected $logs              = null;
    protected $campaign_service  = null;
    protected $batch_no          = null;
    protected $original_batch_no = null;
    protected $original_batch    = null;
    protected $batch_file        = null;
    protected $is_last           = false;
    protected $campaign_short_urls = [];

    const QUEUE_KEY = 'CSV_generate_processing';

    /**
     * Create a new job instance.
     */
    public function __construct(array $params)
    {
        $this->campaign_service  = new CampaignService(app()->make(CampaignRepositoryInterface::class));

        $this->url_shortener     = $params['url_shortener']     ?? $this->url_shortener;
        $this->batch_no          = $params['batch_no']          ?? $this->batch_no;
        $this->original_batch_no = $params['original_batch_no'] ?? $this->original_batch_no;
        $this->original_batch    = $params['original_batch']    ?? $this->original_batch;
        $this->offset            = $params['offset']            ?? $this->offset;
        $this->batchSize         = $params['batchSize']         ?? $this->batchSize;
        $this->batch_file        = $params['batch_file']        ?? $this->batch_file;
        $this->is_last           = $params['is_last']           ?? $this->is_last;
        $this->campaign_short_urls = $params['campaign_short_urls'] ?? $this->campaign_short_urls;

        $this->onQueue(self::QUEUE_KEY);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ignored_campaigns = Campaign::select('id')->where('is_ignored_on_queue', true)->pluck('id');

        $this->logs = BroadcastLog::query()
            ->with(['campaign', 'message'])
            ->whereNotIn('campaign_id', $ignored_campaigns)
            ->where('batch', $this->original_batch_no)
            ->limit($this->batchSize)
            ->get();

        if ($this->logs->isEmpty()) {
            Log::info("REgenerateJob (batch file no: ". $this->original_batch_no .") -> No matching entries found - break...");

            $this->batch_file->update([
                'is_ready' => 1,
                'has_errors' => 1
            ]);

            return;
        }

        Log::info("REgenerateJob (batch file id: ". $this->original_batch->id .") -> to id:". $this->batch_file->id ." logs count: " . count($this->logs) . ' Batch no: ' . $this->batch_no . ' Offset: ' . $this->offset);

        $ids = [];
        $cases = '';
        $casesCount = 0;

        if (!$this->url_shortener) {
            Log::debug("REgenerateJob (batch file id: ". $this->batch_file->id .") -> campaign_short_url doesnt exist - break...");

            $this->batch_file->update(['has_errors' => 1]);

            return;
        }

        foreach ($this->logs as $log) {
            if (!$log->message) {
                Log::error("REgenerateJob (batch file id: ". $this->batch_file->id .") -> Message not found for log id - " . $log->id . ' - skipping...');

                continue;
            }

            if (!$log->campaign) {
                Log::error("REgenerateJob (batch file id: ". $this->batch_file->id .") -> Campaign not found for log id - " . $log->id . ' - skipping...');

                continue;
            }

            $ids[] = "'". $log->id ."'";
            $message = $log->message;
            $campaign = $log->campaign;

            $campaign_short_url = isset($this->campaign_short_urls[$campaign->id]) ? $this->campaign_short_urls[$campaign->id] : null;

            if (!$campaign_short_url) {
                Log::debug('REgenerateJob -> campaign_short_url doesnt exist for log id ' . $log->id . ' - skipping...', [
                    'log' => $log,
                    'campaign' => $campaign,
                ]);

                continue;
            }

            $generated_url = $this->campaign_service->generateUrlForCampaignFromAlias($campaign_short_url->url_shortener, $log->slug);
            $message_body = $message->getParsedMessage($generated_url);

            $cases .= "WHEN '{$log->id}' THEN '" . addslashes($message_body) . "'";
            $casesCount++;
        }

        if ($casesCount == 0) {
            Log::error("REgenerateJob (batch file id: ". $this->batch_file->id .") -> No cases created - break...");
            $this->batch_file->update([
                'has_errors' => 1,
            ]);

            return;
        }

        $idList = implode(",", $ids);

        try {
            $sql = "UPDATE `broadcast_logs`
            SET `message_body` = CASE `id`
                {$cases}
            END,
            `is_downloaded_as_csv` = 0,
            `batch` = '$this->batch_no',
            `updated_at` = NOW()
            WHERE `id` IN ({$idList})";

            \DB::statement($sql);

        } catch (\Exception $e) {
            Log::error("REgenerateJob (batch file id: ". $this->batch_file->id .") -> Error updating broadcast_logs: " . $e->getMessage());
            $this->batch_file->update(['has_errors' => 1]);
        } finally {
            if ($this->is_last == true) {
                $this->batch_file->update([
                    'is_ready' => 1,
                    'generated_count' => $this->batch_file->generated_count + $casesCount,
                ]);
            } else {
                $this->batch_file->increment('generated_count', $casesCount);
            }

            $this->original_batch->decrement('number_of_entries', $casesCount);
        }
    }
}
