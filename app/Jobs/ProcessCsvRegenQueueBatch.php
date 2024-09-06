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
use App\Models\Campaign;
use App\Models\BatchFile;
use App\Models\UrlShortener;
use App\Models\Message;
use App\Services\Campaign\CampaignService;
use App\Repositories\Model\CampaignShortUrl\CampaignShortUrlRepository;

use App\Repositories\Model\UrlShortener\UrlShortenerRepository;
use App\Repositories\Contract\UrlShortener\UrlShortenerRepositoryInterface;


use Illuminate\Support\Facades\Log;

class ProcessCsvRegenQueueBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $offset = 0;
    protected $batchSize = 500;
    protected $url_shortener = null;
    protected $logs = null;
    protected $campaign_service = null;
    protected $batch_no = null;
    protected $original_batch_no = null;
    protected $batch_file = null;
    protected $is_last = false;
    protected $type = 'fifo';
    protected $type_id = null;
    protected $message_id = null;
    protected $campaignShortUrlRepository = null;
    protected $urlShortenerRepository = null;
    protected $campaignRepository = null;
    protected $broadcastLogRepository = null;

    /**
     * Create a new job instance.
     */
    public function __construct( $offset, $batchSize, $url_shortener = null, $original_batch_no, $batch_no, $batch_file, $is_last, $type = 'fifo', $type_id = null, $message_id = null        
                                    
    )
    {
        $this->campaignShortUrlRepository = new CampaignShortUrlRepository(new CampaignShortUrl());
        $this->urlShortenerRepository = app()->make(UrlShortenerRepositoryInterface::class);
        $this->url_shortener = $url_shortener != null ? $url_shortener:$this->url_shortener ;
        $this->campaign_service = new CampaignService();
        $this->batch_no = $batch_no;
        $this->original_batch_no = $original_batch_no;
        $this->offset = $offset;
        $this->batchSize = $batchSize;
        $this->batch_file = $batch_file;
        $this->is_last = $is_last;
        $this->type = $type;
        $this->type_id = $type_id;
        $this->message_id = $message_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $unique_campaigns = collect();
        $unique_campaign_map = [];

        $new_campaigns = collect();
        $campaign_service = $this->campaign_service;
        $current_campaign_id = 0;
        $message = '';
        $batch_no = $this->batch_no;
        $original_batch_no = $this->original_batch_no;
        $url_shortener = $this->url_shortener;
        $domain_id = UrlShortener::where('name', $url_shortener)->first()->asset_id;
        $ignored_campaigns = Campaign::select('id')->where('is_ignored_on_queue', true)->get()->pluck('id');
        if($this->type == 'fifo'){
            $this->logs = BroadcastLog::select()->whereNotIn('campaign_id', $ignored_campaigns)->where('batch', $this->original_batch_no)->orderby('id', 'ASC')->offset($this->offset)->limit($this->batchSize)->get();
        }
        if($this->type == 'campaign'){
            $this->logs = BroadcastLog::select()->whereNotIn('campaign_id', $ignored_campaigns)->where('batch', $this->original_batch_no)->where('campaign_id', $this->type_id)->orderby('id', 'ASC')->offset($this->offset)->limit($this->batchSize)->get();
        }

        Log::info('Grabbed '.count($this->logs).' logs to process - batch no - '.$this->batch_no.' - Offset - '.$this->offset);
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

            if($this->message_id != null){
                $message = Message::find($this->message_id);
            }

            if ($message) {
                // check if there an existing URL for this campaign with the same domain
                if(isset($campaign_short_url_map[$campaign->id])){
                    $campaign_short_url = $campaign_short_url_map[$campaign->id];
                }else{
                    // is there an existing campaign url alias for this campaign with the same domain?
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
                    Log::info('campaign_short_url existed in the database');
                    Log::info($campaign_short_url->toArray());
                    // hack in case we have an entry but no keitaro reference
                    if(!$campaign_short_url->keitaro_campaign_id){
                        $_new_campaign = [
                            'campaign_short_url_id'=>$campaign_short_url->id,
                            'url_shortener'=>$url_shortener, 
                            'campaign'=>$campaign,
                            'domain_id'=>$domain_id,
                            'alias'=>$alias_for_campaign
                        ];
    
                        $new_campaigns->add($_new_campaign);
    
                    }

                }else{
                    // there is no campaign entry
                    $alias_for_campaign = uniqid();
                    Log::info('campaign_short_url generated from uniqid ');

                    // make a spoof entry for campaign url
                    $_url_shortener = $this->urlShortenerRepository->search(['name'=>$url_shortener]);
                    $url_for_keitaro = $campaign_service->generateUrlForCampaign($url_shortener, $alias_for_campaign);

                    $_campaign_short_url = $this->campaignShortUrlRepository->create([
                        'campaign_id' => $campaign->id,
                        'url_shortener' => $url_for_keitaro,    // store reference to the short domain <-> campaign
                        'campaign_alias' => $alias_for_campaign,
                        'url_shortener_id'=>$_url_shortener->id,
                        'deleted_on_keitaro'=>false
                    ]);

                    $campaign_short_url_map[$campaign->id] = $_campaign_short_url;
                    Log::info((array)$campaign_short_url_map[$campaign->id]->toArray());

                    $_new_campaign = [
                        'campaign_short_url_id'=>$_campaign_short_url->id,
                        'url_shortener'=>$url_shortener, 
                        'campaign'=>$campaign,
                        'domain_id'=>$domain_id,
                        'alias'=>$alias_for_campaign
                    ];

                    $new_campaigns->add($_new_campaign);
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


        /*  $unique_campaigns->each(function ($itemCollection) use ($url_shortener, $unique_campaign_map, $campaign_service, $domain_id) {
            $alias_for_campaign = $itemCollection['alias'];
            $item = $itemCollection['campaign'];

            $url_for_keitaro = $campaign_service->generateUrlForCampaign($url_shortener, $alias_for_campaign);
            $url_for_campaign = $item->message?->target_url;
            // maintain a record of short urls created against a campaign
            // if a short url has been created against a campaign - do not create a new one, use the existing one
            // else create a new one
            if (!$this->campaignShortUrlRepository->findWithCampaignIDUrlID($item->id, $url_for_keitaro)) {
                Log::info('Campaign Short URL for  Campaign ID - '.$item->id.' and URL '.$url_for_keitaro.' was not found so created a new one');

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
        }); */

        $idList = implode(',', $ids);
        $caseList = implode(' ', $cases);
        $is_downloaded_as_csv = 1;
        
        Log::info('Number of log entries updated - '.count($ids).' - with number of cases - '.count($cases));

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
        Log::info('new_campaigns ', $new_campaigns->toArray());

        /* $new_campaigns->each(function($_item) use ($campaign_service, $domain_id){
            $url_for_keitaro = $campaign_service->generateUrlForCampaign($_item['url_shortener'], $_item['alias']);
            $url_for_campaign = $_item['campaign']->message?->target_url;
            $response_campaign = $campaign_service->createCampaignOnKeitaro($_item['alias'], $_item['campaign']->title, $_item['campaign']->keitaro_group_id, $domain_id);
            $response_flow = $campaign_service->createFlowOnKeitaro($response_campaign['id'],  $_item['campaign']->title, $url_for_campaign);
            $_url_shortener = $this->urlShortenerRepository->search(['name'=>$_item['url_shortener']]);

            $this->campaignShortUrlRepository->updateByID([
                'flow_id' => $response_flow['id'],
                'response' => @json_encode($response_flow),
                'keitaro_campaign_id' => $response_campaign['id'],
                'keitaro_campaign_response' => @json_encode($response_campaign),
            ], $_item['campaign_short_url_id'] );


        });
        */


        if($this->is_last == true){
            Log::info('IS THE LAST ONE');
            $this->batch_file->is_ready = 1;
            $this->batch_file->save();
        }


}
}
