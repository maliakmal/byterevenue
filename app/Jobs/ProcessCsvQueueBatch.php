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
use App\Models\UrlShortener;
use App\Services\Campaign\CampaignService;
use App\Repositories\Model\CampaignShortUrl\CampaignShortUrlRepository;
use App\Repositories\Contract\UrlShortener\UrlShortenerRepositoryInterface;

use Illuminate\Support\Facades\Log;

class ProcessCsvQueueBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $offset        = 1;
    protected $batchSize     = 1;
    protected $url_shortener = null;
    protected $logs          = null;
    protected $batch_no      = null;
    protected $batch_file    = null;
    protected $is_last       = false;
    protected $type          = 'fifo';
    protected $type_id       = null;
    protected $message_id    = null;
    protected $campaignShortUrlRepository = null;
    protected $urlShortenerRepository     = null;
    protected $broadcastLogRepository     = null;

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
        $this->type_id       = $params['type_id']       ?? $this->type_id;
        $this->message_id    = $params['message_id']    ?? $this->message_id;
        $this->offset        = $params['offset']        ?? $this->offset;
        $this->batchSize     = $params['batchSize']     ?? $this->batchSize;
        $this->batch_file    = $params['batch_file']    ?? $this->batch_file;
        $this->is_last       = $params['is_last']       ?? $this->is_last;
    }

    /**
     * Execute the job.
     */
    public function handle(CampaignService $campaign_service): void
    {
        $unique_campaigns = collect();
        $unique_campaign_map = [];
        $new_campaigns = collect();
        $current_campaign_id = 0;
        $message = '';
        $batch_no = $this->batch_no;
        $url_shortener = $this->url_shortener;
        $domain_id = UrlShortener::where('name', $url_shortener)->first()->asset_id;
        $ignored_campaigns = Campaign::select('id')->where('is_ignored_on_queue', true)->get()->pluck('id');

        $query = BroadcastLog::query()
            ->whereNotIn('campaign_id', $ignored_campaigns)
            ->whereNull('batch')
            ->limit($this->batchSize)
            ->offset($this->offset);

        if ($this->type == 'campaign') {
            $query->where('campaign_id', $this->type_id);
        }

        $this->logs = $query->get();

        Log::info('Grabbed ' . count($this->logs) . ' logs to process - batch no - ' . $this->batch_no . ' - Offset - ' . $this->offset);
        $ids = [''];
        $cases = ["WHEN '' THEN ''"];
        $batch = $batch_no;

        $campaign_short_url_map = [];

        foreach ($this->logs as $log) {

            if ($log->campaign_id != $current_campaign_id) {
                if ($log->campaign_id == 0) {
                    $message = $log->message;
                    $campaign = $message->campaign;
                } else {
                    $campaign = $log->campaign;
                    $current_campaign_id = $log->campaign->id;
                    $message = $log->message;
                }
            }

            if ($this->message_id != null) {
                $message = Message::find($this->message_id);
            }

            if ($message) {
                // check if there an existing URL for this campaign with the same domain
                if (isset($campaign_short_url_map[$campaign->id])) {
                    $campaign_short_url = $campaign_short_url_map[$campaign->id];
                } else {
                    // is there an existing campaign url alias for this campaign with the same domain?
                    $campaign_short_url = CampaignShortUrl::select()->where('campaign_id', $campaign->id)->where('url_shortener', 'like', '%' . $url_shortener . '%')->orderby('id', 'desc')->first();
                    $campaign_short_url_map[$campaign->id] = $campaign_short_url;
                }

                // if yes - use that

                if ($campaign_short_url) {
                    if (strstr($campaign_short_url->url_shortener, DIRECTORY_SEPARATOR)) {
                        $alias_for_campaign = explode('?', explode(DIRECTORY_SEPARATOR, $campaign_short_url->url_shortener)[1])[0];
                    } else {
                        $alias_for_campaign = $campaign_short_url->url_shortener;
                    }

                    // hack in case we have an entry but no keitaro reference
                    if (!$campaign_short_url->keitaro_campaign_id) {
                        $_new_campaign = [
                            'campaign_short_url_id' => $campaign_short_url->id,
                            'url_shortener' => $url_shortener,
                            'campaign' => $campaign,
                            'domain_id' => $domain_id,
                            'alias' => $alias_for_campaign
                        ];

                        $new_campaigns->add($_new_campaign);
                    }

                } else {
                    // there is no campaign entry
                    $alias_for_campaign = uniqid();
                    Log::info('campaign_short_url generated from uniqid ');

                    // make a spoof entry for campaign url
                    $_url_shortener = $this->urlShortenerRepository->search(['name' => $url_shortener]);
                    $url_for_keitaro = $campaign_service->generateUrlForCampaign($url_shortener, $alias_for_campaign);

                    $_campaign_short_url = $this->campaignShortUrlRepository->create([
                        'campaign_id' => $campaign->id,
                        'url_shortener' => $url_for_keitaro,    // store reference to the short domain <-> campaign
                        'campaign_alias' => $alias_for_campaign,
                        'url_shortener_id' => $_url_shortener->id,
                        'deleted_on_keitaro' => false
                    ]);

                    $campaign_short_url_map[$campaign->id] = $_campaign_short_url;

                    $_new_campaign = [
                        'campaign_short_url_id' => $_campaign_short_url->id,
                        'url_shortener' => $url_shortener,
                        'campaign' => $campaign,
                        'domain_id' => $domain_id,
                        'alias' => $alias_for_campaign
                    ];

                    $new_campaigns->add($_new_campaign);
                }

                $generated_url = $campaign_service->generateUrlForCampaign($url_shortener, $alias_for_campaign, $log->id);

                $message_body = $message->getParsedMessage($generated_url);
                $cases[] = "WHEN '{$log->id}' THEN '" . addslashes($message_body) . "'";

                $campaign_key = $campaign->id . '';
                if ($campaign && isset($unique_campaign_map[$campaign_key]) == false) {
                    $unique_campaigns->add(['campaign' => $campaign, 'alias' => $alias_for_campaign]);
                    $unique_campaign_map[$campaign_key] = true;
                }
            }

            $ids[] = $log->id;
        }

        $idList = "'". implode("','", $ids) ."'";
        $caseList = implode(' ', $cases);

        Log::info('Number of log entries updated - ' . count($ids) . ' - with number of cases - ' . count($cases));

        $sql = "UPDATE `broadcast_logs`
        SET `message_body` = CASE `id`
            {$caseList}
        END,
        " . ($this->message_id != null ? " `message_id` = '$this->message_id', " : "") . "
        `is_downloaded_as_csv` = 1,
        `batch` = '$batch',
        `updated_at` = NOW()
        WHERE `id` IN ({$idList})";

        \DB::statement($sql);

        if ($this->is_last == true) {
            Log::info('IS THE LAST ONE');
            $this->batch_file->is_ready = 1;
            $this->batch_file->save();
        }
    }
}
