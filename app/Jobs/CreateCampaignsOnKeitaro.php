<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Campaign\CampaignService;
use Illuminate\Support\Facades\Log;
use App\Models\CampaignShortUrl;
use App\Models\Campaign;
use App\Repositories\Model\Campaign\CampaignRepository;
use App\Repositories\Model\UrlShortener\UrlShortenerRepository;
use App\Repositories\Contract\UrlShortener\UrlShortenerRepositoryInterface;
use App\Repositories\Model\CampaignShortUrl\CampaignShortUrlRepository;

class CreateCampaignsOnKeitaro implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $params = null;
    protected $campaignRepository = null;
    protected $urlShortenerRepository = null;
    protected $campaignShortUrlRepository = null;

    /**
     * Create a new job instance.
     */
    public function __construct($params)
    {
        //
        $this->params = $params;
        $this->campaignRepository = new CampaignRepository(new Campaign());
        $this->urlShortenerRepository = app()->make(UrlShortenerRepositoryInterface::class);
        $this->campaignShortUrlRepository = new CampaignShortUrlRepository(new CampaignShortUrl());
    }

    /**
     * Execute the job.
     */
    public function handle(CampaignService $campaign_service): void
    {
        $domain_id = $this->params['domain_id'];

        foreach($this->params['campaigns'] as $_item){
            Log::info('_item');
            Log::info($_item);
            $url_for_keitaro = $campaign_service->generateUrlForCampaign($_item['url_shortener'], $_item['campaign_alias']);
            $campaign = $this->campaignRepository->find($_item['campaign_id']);
            $url_for_campaign = $campaign->message?->target_url;
            Log::info($_item['campaign_alias']);
            $response_campaign = $campaign_service->createCampaignOnKeitaro($_item['campaign_alias'], $campaign->title, $campaign->keitaro_group_id, $domain_id);
            $response_flow = $campaign_service->createFlowOnKeitaro($response_campaign['id'],  $campaign->title, $url_for_campaign);
            $_url_shortener = $this->urlShortenerRepository->search(['name'=>$_item['url_shortener']]);

            $this->campaignShortUrlRepository->updateByID([
                'flow_id' => $response_flow['id'],
                'response' => @json_encode($response_flow),
                'keitaro_campaign_id' => $response_campaign['id'],
                'keitaro_campaign_response' => @json_encode($response_campaign),
            ], $_item->id);


        };
    }
}
