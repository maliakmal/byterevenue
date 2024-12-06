<?php

namespace App\Jobs;

use App\Models\UrlShortener;
use App\Repositories\Contract\UrlShortener\UrlShortenerRepositoryInterface;
use App\Services\Domain\DomainService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use League\Csv\Exception;

class CheckDomainPropagationJob extends BaseJob implements ShouldQueue
{
    private DomainService $domainService;
    private UrlShortenerRepositoryInterface $urlShortenerRepository;

    public $telemetry = true;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $min_created_at = Carbon::now()->subMonths(3);
        $this->domainService = new DomainService();
        $this->urlShortenerRepository = app()->make(UrlShortenerRepositoryInterface::class);
        $query = UrlShortener::where('is_propagated', false)
            ->whereNotNull('asset_id')
            ->where('created_at' ,'>=', $min_created_at);

        $query->chunk(100, function ($urls) {
            foreach ($urls as $url) {
                try {
                    $keitaro_domain_id = $url->asset_id;
                    if($this->domainService->isDomainPropaginated($keitaro_domain_id)){
                        if(!$this->urlShortenerRepository->update([
                            'is_propagated' => true
                        ], $url->id)){
                            throw  new Exception('error update url shortener is_propagation');
                        }
                    }
                } catch (\Exception $exception){
                    report($exception);
                }
            }
        });
    }
}
