<?php

namespace App\Jobs;

use App\Models\UrlShortener;
use App\Services\Keitaro\KeitaroCaller;
use App\Services\Keitaro\Requests\Domains\RegisterShortDomainRequest;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Register short domains after bulk creating records (db) in the UrlShortenerService (create method)
 *
 * Class RegisterShortDomainJob
 * @package App\Jobs
 */
class RegisterShortDomainJob extends BaseJob implements ShouldQueue
{
    public $timeout = 600; // 10 minutes
    public $tries = 1;

    const QUEUE_KEY = 'default';

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue(self::QUEUE_KEY);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info('Start process register domains');

        UrlShortener::where('response', null)
            ->where('asset_id', null)
            ->where('is_registered', false)
            ->where('is_propagated', false)
            ->where('has_error', false)
            ->get()
            ->each(function ($urlShortener) {
                $this->registerDomain($urlShortener);
            });
    }

    protected function registerDomain(UrlShortener $urlShortener): void
    {
        $request = new RegisterShortDomainRequest(
            name: $urlShortener->name,
            ssl_redirect: true,
            is_ssl: true,
            cloudflare_proxy: true,
            allow_indexing: false
        );

        try {
            $rawResponse = KeitaroCaller::call($request);

            if (isset($rawResponse['error'])) {
                throw new \Exception($rawResponse['error']);
            }

            $response = $rawResponse[0];

            $urlShortener->update([
                'is_registered' => true,
                'is_propagated' => false,
                'asset_id' => $response['id'],
                'response' => json_encode($response),
            ]);

        } catch (\Exception $e) {
            $urlShortener->update([
                'has_error' => true,
                'response' => $e->getMessage(),
            ]);
        }
    }
}
