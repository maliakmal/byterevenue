<?php

namespace App\Jobs;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\BroadcastLog;
use App\Models\CampaignShortUrl;
use App\Models\BatchFile;
use App\Models\UrlShortener;
use App\Services\Campaign\CampaignService;
use App\Repositories\Model\CampaignShortUrl\CampaignShortUrlRepository;

use App\Repositories\Model\UrlShortener\UrlShortenerRepository;
use App\Repositories\Contract\UrlShortener\UrlShortenerRepositoryInterface;


use Illuminate\Support\Facades\Log;

class ProcessCsvQueueBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $offset = 0;
    protected $batchSize = 500;
    protected $url_shortener = null;
    protected $logs = null;
    protected $campaign_service = null;
    protected $batch_no = null;
    protected $batch_file = null;
    protected $is_last = false;
    protected $campaignShortUrlRepository = null;
    protected $urlShortenerRepository = null;
    protected $campaignRepository = null;
    protected $broadcastLogRepository = null;

    /**
     * Create a new job instance.
     */
    public function __construct( $offset, $batchSize, $url_shortener = null, $batch_no, $batch_file, $is_last,         
                                    
    )
    {
        $this->campaignShortUrlRepository = new CampaignShortUrlRepository(new CampaignShortUrl());
        $this->urlShortenerRepository = app()->make(UrlShortenerRepositoryInterface::class);
        $this->url_shortener = $url_shortener != null ? $url_shortener:$this->url_shortener ;
        $this->campaign_service = new CampaignService();
        $this->batch_no = $batch_no;
        $this->offset = $offset;
        $this->batchSize = $batchSize;
        $this->batch_file = $batch_file;
        $this->is_last = $is_last;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $unique_campaigns = collect();
        $unique_campaign_map = [];
        $campaign_service = $this->campaign_service;
        $current_campaign_id = 0;
        $message = '';
        $batch_no = $this->batch_no;
        $url_shortener = $this->url_shortener;
        $domain_id = UrlShortener::where('name', $url_shortener)->first()->asset_id;
        $this->logs = BroadcastLog::select()->whereNull('batch')->orderby('id', 'ASC')->offset($this->offset)->limit($this->batchSize)->get();

        $ids = [0];
        $cases = ["WHEN 0 THEN ''"];
        $bindings = [];
        $batch = $batch_no;

        $campaign_short_url_map = [];

        foreach ($this->logs as $log) {
            $message_body = '';
            $is_downloaded_as_csv = 0;

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
            if ($message) {
                // allocate a uniq id to the campaign if it doesnt have one already
                // check if there an existing URL for this campaign with the same domain
                if(isset($campaign_short_url_map[$campaign->id])){
                    $campaign_short_url = $campaign_short_url_map[$campaign->id];
                }else{
                    $campaign_short_url = CampaignShortUrl::select()->where('campaign_id', $campaign->id)->where('url_shortener', 'like', '%'.$url_shortener.'%')->orderby('id', 'desc')->first();
                    $campaign_short_url_map[$campaign->id] = $campaign_short_url;
                }

                // if yes - use that

                if($campaign_short_url){
                    if(strstr($campaign_short_url->url_shortener, DIRECTORY_SEPARATOR)){
                        $alias_for_campaign = explode('?', explode(DIRECTORY_SEPARATOR,  $campaign_short_url->url_shortener)[1])[0];
                    }else{
                        $alias_for_campaign = $campaign_short_url->url_shortener;
                    }
                }else{
                    $alias_for_campaign = uniqid();
                    $campaign_short_url_map[$campaign->id] = (object)['url_shortener'=>$alias_for_campaign];

                }

                $generated_url = $campaign_service->generateUrlForCampaign($url_shortener, $alias_for_campaign, $log->id);

                $message_body = $message->getParsedMessage($generated_url);
                $cases[] = "WHEN {$log->id} THEN '".addslashes($message_body)."'";

                $campaign_key = $campaign->id . '';
                if ($campaign && isset($unique_campaign_map[$campaign_key]) == false) {
                    $unique_campaigns->add(['campaign'=>$campaign, 'alias'=>$alias_for_campaign]);
                    $unique_campaign_map[$campaign_key] = true;
                }
            }
            $ids[] =  $log->id;


        
            // $log->is_downloaded_as_csv = 1;
            // $log->batch = $batch_no;
            // $log->save();

        }

        $idList = implode(',', $ids);
        $caseList = implode(' ', $cases);
        $is_downloaded_as_csv = 1;
        $sql = "
        UPDATE `broadcast_logs`

        SET `message_body` = CASE `id` 
            {$caseList} 
        END,

        `is_downloaded_as_csv` = 1,
        `batch` = '$batch',
        `updated_at` = NOW()
        WHERE `id` IN ({$idList})
    ";

        DB::statement($sql);

        if($this->is_last == true){
            $this->batch_file->is_ready = 1;
            $this->batch_file->save();
        }

        $unique_campaigns->each(function ($itemCollection) use ($url_shortener, $unique_campaign_map, $campaign_service, $domain_id) {
            $alias_for_campaign = $itemCollection['alias'];
            $item = $itemCollection['campaign'];

            $url_for_keitaro = $campaign_service->generateUrlForCampaign($url_shortener, $alias_for_campaign);
            $url_for_campaign = $item->message?->target_url;
            // maintain a record of short urls created against a campaign
            // if a short url has been created against a campaign - do not create a new one, use the existing one
            // else create a new one
            if (!$this->campaignShortUrlRepository->findWithCampaignIDUrlID($item->id, $url_for_keitaro)) {

                $response_campaign = $campaign_service->createCampaignOnKeitaro($alias_for_campaign, $item->title, $item->keitaro_group_id, $domain_id);
                $response_flow = $campaign_service->createFlowOnKeitaro($response_campaign['id'], $item->title, $url_for_campaign);
                $_url_shortener = $this->urlShortenerRepository->search(['name'=>$url_shortener]);

                $this->campaignShortUrlRepository->create([
                    'campaign_id' => $item->id,
                    'url_shortener' => $url_for_keitaro,    // store reference to the short domain <-> campaign
                    'flow_id' => $response_flow['id'],
                    'response' => @json_encode($response_flow),
                    'keitaro_campaign_id' => $response_campaign['id'],
                    'keitaro_campaign_response' => @json_encode($response_campaign),
                    'campaign_alias' => $alias_for_campaign,
                    'url_shortener_id'=>$_url_shortener->id
                ]);

            }
        });

}
}
