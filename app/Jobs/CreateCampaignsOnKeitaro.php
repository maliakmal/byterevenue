<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Campaign\CampaignService;
use Illuminate\Support\Facades\Log;
use App\Models\CampaignShortUrl;
use App\Models\Campaign;
use App\Repositories\Model\Campaign\CampaignRepository;
use App\Repositories\Contract\UrlShortener\UrlShortenerRepositoryInterface;
use App\Repositories\Model\CampaignShortUrl\CampaignShortUrlRepository;

class CreateCampaignsOnKeitaro extends BaseJob implements ShouldQueue
{
    protected $params = null;
    protected $campaignRepository = null;
    protected $urlShortenerRepository = null;
    protected $campaignShortUrlRepository = null;

    public $telemetry = false;

    /**
     * Create a new job instance.
     */
    public function __construct($params)
    {
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

        foreach($this->params['campaigns'] as $_item) {
            $campaign = $this->campaignRepository->find($_item['campaign_id']);
            $url_for_campaign = $campaign->message?->target_url;

            $response_campaign = $campaign_service->createCampaignOnKeitaro($_item['campaign_alias'], $campaign->title, $campaign->keitaro_group_id, $domain_id);

            if ($response_campaign['error'] ?? null) {
                Log::error('(CreateCampaignsOnKeitaro) keitaro response error', ['error message: ' => $response_campaign['error']]);
                $this->campaignShortUrlRepository->updateByID([
                    'error' => @json_encode($response_campaign),
                ], $_item->id);

                continue;
            }

            $response_flow = $campaign_service->createFlowOnKeitaro($response_campaign['id'],  $campaign->title, $url_for_campaign);

            $this->campaignShortUrlRepository->updateByID([
                'flow_id' => $response_flow['id'],
                'response' => @json_encode($response_flow),
                'keitaro_campaign_id' => $response_campaign['id'],
                'keitaro_campaign_response' => @json_encode($response_campaign),
            ], $_item->id);
        }
    }
}
