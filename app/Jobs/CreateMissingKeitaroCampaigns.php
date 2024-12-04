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

class CreateMissingKeitaroCampaigns implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $campaignRepository = null;
    protected $urlShortenerRepository = null;
    protected $campaignShortUrlRepository = null;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->campaignRepository = new CampaignRepository(new Campaign());
        $this->urlShortenerRepository = app()->make(UrlShortenerRepositoryInterface::class);
        $this->campaignShortUrlRepository = new CampaignShortUrlRepository(new CampaignShortUrl());
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        dd('stop'); // abandoned?
//        $incomplete = $this->campaignShortUrlRepository->getIncomplete();
//        foreach($incomplete as $one){
//            $campaign = $this->campaignRepository->find($incomplete->campaign_id);
//
//            $url_for_campaign = $campaign->message?->target_url;
//
//            $campaign_alias = array_pop(explode(DIRECTORY_SEPARATOR, $one->url_shortener));
//
//            $_url_shortener = $one->urlShortener();
//
//            $response_campaign = $campaign_service->createCampaignOnKeitaro($campaign_alias, $campaign->title, $campaign->keitaro_group_id, $_url_shortener->asset_id);
//
//            if ($response_campaign['error'] ?? null) {
//                \Log::error('(CreateMissingKeitaroCampaignsJob) keitaro response error', ['error message: ' => $response_campaign['error']]);
//                continue;
//            }
//
//            $response_flow = $campaign_service->createFlowOnKeitaro($response_campaign['id'],  $campaign->title, $url_for_campaign);
//
//            $this->campaignShortUrlRepository->updateByID([
//                'flow_id' => $response_flow['id'],
//                'response' => @json_encode($response_flow),
//                'keitaro_campaign_id' => $response_campaign['id'],
//                'keitaro_campaign_response' => @json_encode($response_campaign),
//            ], $one->id);
//
//        }
//
    }
}
