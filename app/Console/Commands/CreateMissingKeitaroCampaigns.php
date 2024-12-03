<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Campaign\CampaignService;
use App\Models\CampaignShortUrl;
use App\Models\Campaign;
use App\Repositories\Model\Campaign\CampaignRepository;
use App\Repositories\Contract\UrlShortener\UrlShortenerRepositoryInterface;
use App\Repositories\Model\CampaignShortUrl\CampaignShortUrlRepository;

class CreateMissingKeitaroCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-missing-keitaro-campaigns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $campaignRepository = null;
    protected $urlShortenerRepository = null;
    protected $campaignShortUrlRepository = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->campaignRepository = new CampaignRepository(new Campaign());
        $this->urlShortenerRepository = app()->make(UrlShortenerRepositoryInterface::class);
        $this->campaignShortUrlRepository = new CampaignShortUrlRepository(new CampaignShortUrl());

        $campaign_service =  new CampaignService($this->campaignRepository);
        $incomplete = $this->campaignShortUrlRepository->getIncomplete();

        $this->info(count($incomplete).' keitaro records need updating (Scheduler)');

        foreach($incomplete as $one){

            $campaign = $this->campaignRepository->find($one->campaign_id);
            $url_for_campaign = $campaign->message?->target_url;

            $_url = explode(DIRECTORY_SEPARATOR, $one->url_shortener);
            $campaign_alias = array_pop($_url);
            $_url_shortener = $one->urlShortener;

            $response_campaign = $campaign_service->createCampaignOnKeitaro($campaign_alias, $campaign->title, $campaign->keitaro_group_id, $_url_shortener->asset_id);

            if ($response_campaign['error'] ?? null) {
                \Log::error('(CreateMissingKeitaroCampaignsCommand) keitaro response error', ['error message: ' => $response_campaign['error']]);
                $this->campaignShortUrlRepository->updateByID([
                    'error' => @json_encode($response_campaign),
                ], $one->id);

                continue;
            }

            $response_flow = $campaign_service->createFlowOnKeitaro($response_campaign['id'],  $campaign->title, $url_for_campaign);

            \Log::info('Keitaro campaign created with ID'.$response_campaign['id'].' for alias '.$campaign_alias);

            $this->campaignShortUrlRepository->updateByID([
                'flow_id' => $response_flow['id'],
                'response' => @json_encode($response_flow),
                'keitaro_campaign_id' => $response_campaign['id'],
                'keitaro_campaign_response' => @json_encode($response_campaign),
            ], $one->id);
        }

        \Log::info('Keitaro Campaign flow task completed');
    }
}
