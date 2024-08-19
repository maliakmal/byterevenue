<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Contract\CampaignShortUrl\CampaignShortUrlRepositoryInterface;
use App\Repositories\Contract\UrlShortener\UrlShortenerRepositoryInterface;

class PrefillDomainIDsForBatches extends Command
{
    private  CampaignShortUrlRepositoryInterface $campaignShortUrlRepository;
    private  UrlShortenerRepositoryInterface $urlShortenerRepository;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prefill-domain-i-ds-for-batches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->campaignShortUrlRepository = app()->make(CampaignShortUrlRepositoryInterface::class);
        $this->urlShortenerRepository = app()->make(UrlShortenerRepositoryInterface::class);

        $campaign_short_urls = $this->campaignShortUrlRepository->search(['url_shortener_is_null'=>true]);
        if(count($campaign_short_urls) == 0){
            $this->info('All clear');
            return;
        }

        foreach($campaign_short_urls as $campaign_short_url):
            $domain = $campaign_short_url->getDomainFromUrlShortener();
            $url_shortener = $this->urlShortenerRepository->search(['name'=>$domain]);
            if($url_shortener){
                $campaign_short_url->url_shortener_id = $url_shortener->id;
                $campaign_short_url->save();
                $this->info('Batch file '.$campaign_short_url->id.' updated with domain '.$domain);
            }
        endforeach;
        $this->info('All clear');
    }
}
