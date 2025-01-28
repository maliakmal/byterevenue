<?php

namespace App\Jobs;

use App\Services\GlobalCachingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Models\CampaignShortUrl;
use App\Services\Campaign\CampaignService;
use App\Repositories\Model\CampaignShortUrl\CampaignShortUrlRepository;
use App\Repositories\Contract\UrlShortener\UrlShortenerRepositoryInterface;
use Illuminate\Support\Facades\Log;

class ProcessCsvQueueBatchByCampaigns extends BaseJob implements ShouldQueue
{
    public $timeout = 600; // 10 minutes
    public $tries = 1;
    public $telemetry = true;

    protected $remainder     = 0;
    protected $batchSize     = 100;
    protected $url_shortener = null;
    protected $logs          = null;
    protected $batch_no      = null;
    protected $batch_file    = null;
    protected $is_last       = false;
    protected $campaign_ids  = null;
    protected $campaign_short_urls = [];
    protected $campaignShortUrlRepository = null;
    protected $urlShortenerRepository     = null;
    protected $broadcastLogRepository     = null;
    protected $cache_service              = null;

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
        $this->remainder     = $params['remainder']     ?? $this->remainder;
        $this->batchSize     = $params['batchSize']     ?? $this->batchSize;
        $this->batch_file    = $params['batch_file']    ?? $this->batch_file;
        $this->is_last       = $params['is_last']       ?? $this->is_last;
        $this->campaign_ids  = $params['campaign_ids']  ?? $this->campaign_ids;
        $this->campaign_short_urls = $params['campaign_short_urls'] ?? $this->campaign_short_urls;
        $this->cache_service       = app()->make(GlobalCachingService::class);

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
            ->whereIn('campaign_id', $this->campaign_ids)
            ->whereNull('batch')
            ->where('is_downloaded_as_csv', 0)
            ->limit($this->batchSize);

        \Log::debug('campaign_ids: ', $this->campaign_ids);

        $this->logs = $query->get();

        if ($this->logs->isEmpty()) {
            Log::error('No matching entries found - skipping...');
            $this->batch_file->update([
                'is_ready'   => 1,
                'has_errors' => 1
            ]);

            return;
        }

        Log::info('GenerateJob -> logs count: ' . count($this->logs) . ' Batch no: ' . $this->batch_no . ' Remainder: ' . $this->remainder);

        $ids = [];
        $cases = '';
        $casesCount = 0;
        $campaign_short_urls = $this->campaign_short_urls;

        //\Log::debug('campaign_short_urls:', ['campaign_short_urls' => $campaign_short_urls]);
        foreach ($this->logs as $log) {
            $ids[]    = "'". $log->id ."'";
            $campaign = $log->campaign;
            $message  = $log->message;

            if (!$message) {
                Log::error('GenerateJob -> Message not found for log id - ' . $log->id . ' - skipping...', [
                    'log:' => $log,
                    'campaign:' => $campaign,
                ]);

                continue;
            }

            $campaign_short_url = $campaign_short_urls->where('campaign_id', $campaign->id)->first();
            //\Log::debug('campaign_short_url:', ['campaign_short_url' => $campaign_short_url]);
            if (!$campaign_short_url) {
                Log::debug('GenerateJob -> campaign_short_url doesnt exist for log id ' . $log->id . ' - skipping...', [
                    'campaign_short_urls:' => $campaign_short_urls,
                    'campaign_short_url:' => $campaign_short_url,
                    'log:' => $log,
                    'campaign:' => $campaign,
                ]);

                continue;
            }

            $generated_url = $campaign_service->generateUrlForCampaignFromAlias($campaign_short_url->url_shortener, $log->slug);
            $message_body = $message->getParsedMessage($generated_url);

            $cases .= "WHEN '{$log->id}' THEN '" . addslashes($message_body) . "'";
            $casesCount++;
        }

        if ($casesCount == 0) {
            Log::error('GenerateJob -> Cases count to create is 0 - break...');
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
            `is_downloaded_as_csv` = 1,
            `batch` = '$batch_no',
            `updated_at` = NOW()
            WHERE `id` IN ({$idList})";

            \DB::statement($sql);

            $this->batch_file->increment('generated_count', $casesCount);

            cache()->put(
                GlobalCachingService::CACHE_PREFIX . 'total_not_downloaded_in_queue',
                cache(GlobalCachingService::CACHE_PREFIX . 'total_not_downloaded_in_queue', 0) - $casesCount,
                GlobalCachingService::DEFAULT_CACHE_TTL
            );
        } catch (\Exception $e) {
            Log::error('GenerateJob -> Error updating broadcast_logs: ' . $e->getMessage());
            $this->batch_file->update(['has_errors' => 1]);
        } finally {
            if ($this->is_last == true) {
                $this->batch_file->update(['is_ready' => 1]);
                $this->cache_service->setWarmingCacheRequest(['global_queue', 'unique_campaigns_ids']);
                \DB::table('export_campaigns_stacks')
                    ->whereIn('campaign_id', $this->batch_file->campaign_ids)
                    ->delete();
            }
        }
    }
}
