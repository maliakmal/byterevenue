<?php

namespace App\Services;

use App\Jobs\CreateCampaignsOnKeitaro;
use App\Jobs\ProcessCsvQueueBatch;
use App\Jobs\ProcessCsvRegenQueueBatch;
use App\Models\BatchFile;
use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Models\CampaignShortUrl;
use App\Models\UrlShortener;
use App\Models\User;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Model\CampaignShortUrl\CampaignShortUrlRepository;
use App\Services\Campaign\CampaignService;
use Illuminate\Support\Facades\Log;

class JobService
{
    public function __construct(
        private CampaignService $campaignService,
        private CampaignShortUrlRepository $campaignShortUrlRepository,
        private BroadcastLogRepositoryInterface $broadcastLogRepository
    ) {}

    /**
     * @return array
     */
    public function index()
    {
        $download_me = null;
        $urlShorteners = UrlShortener::withCount('campaignShortUrls')->onlyRegistered()->orderby('id', 'desc')->get();
        $files = BatchFile::with('urlShortener')->withCount('campaigns')->orderby('id', 'desc')->paginate(15);

        // get count of all messages in the queue
        $queue_stats = $this->broadcastLogRepository->getQueueStats();
        $params['total_in_queue'] = $queue_stats['total_in_queue'];//BroadcastLog::select()->count();
        $params['files'] = $files;
        $params['download_me'] = $download_me;
        $params['urlShorteners'] = $urlShorteners;
        $params['total_not_downloaded_in_queue'] = $queue_stats['total_not_downloaded_in_queue'];// BroadcastLog::select()->where('is_downloaded_as_csv', 0)->count();

        return $params;
    }

    /**
     * @param $uniq_campaign_ids
     * @param $urlShortenerName
     * @param $urlShortener
     *
     * @return array
     */
    private function createCampaignShortUrls($uniq_campaign_ids, $urlShortenerName, $urlShortener)
    {
        $campaign_short_urls = [];

        foreach ($uniq_campaign_ids as $uniq_campaign_id) {

            if (!$this->campaignShortUrlRepository->findWithCampaignIDUrlID($uniq_campaign_id, $urlShortenerName)) {
                $alias_for_campaign = uniqid();
                $url_for_keitaro = $this->campaignService->generateUrlForCampaign($urlShortenerName, $alias_for_campaign);

                $_campaign_short_url = $this->campaignShortUrlRepository->create([
                    'campaign_id' => $uniq_campaign_id,
                    'url_shortener' => $url_for_keitaro,    // store reference to the short domain <-> campaign
                    'campaign_alias' => $alias_for_campaign,
                    'url_shortener_id' => $urlShortener->id,
                    'deleted_on_keitaro' => false
                ]);

                $campaign_short_urls[] = $_campaign_short_url;
            }
        }

        return $campaign_short_urls;
    }

    public function processGenerate(array $params, $needFullResponse = null)
    {
        $requestCount      = intval($params['number_messages']); // count of records in CSV
        $urlShortenerName  = trim($params['url_shortener']);
        $type = 'campaign' === $params['type'] ? 'campaign' : 'fifo';
        $campaign_ids      = $params['campaign_ids'] ?? [];

        Log::alert('Request for CSV generation (WEB) Starting process...', $params);

        $urlShortener = UrlShortener::where('name', $urlShortenerName)->first();
        $domain_id = $urlShortener->asset_id;
        $campaign_short_urls = [];
        $campaign_short_urls_new = [];
        $batchSize = 1000; // ids scope for each job
        $type = 'campaign' === $type ? 'campaign' : 'fifo';

        // total count of batches for the job
        $ignored_campaigns = Campaign::select('id')->where('is_ignored_on_queue', true)->pluck('id')->toArray();
        // TODO:: is_ignored_on_queue - is blacklisted campaign? mb separate table?

        $totalRecords = BroadcastLog::query()
            ->whereNotIn('campaign_id', $ignored_campaigns)
            ->whereNull('batch')
            ->when('campaign' === $type, function ($query) use ($campaign_ids) {
                $query->whereIn('campaign_id', $campaign_ids);
            })
            ->count();

        if (0 == $totalRecords) {
            // redirect()->route('jobs.index')->with('error', 'No messages ready for CSV generation.');
            return ['error' => 'No messages ready for CSV generation.'];
        }

        $allowedCompanyIds = array_values(array_diff($campaign_ids ?? [], $ignored_campaigns));
        $campaign_ids = 'campaign' === $type ?
            Campaign::whereIn('id', $allowedCompanyIds)->pluck('id')->toArray() :
            $this->broadcastLogRepository->getUniqueCampaignsIDs($requestCount, $ignored_campaigns);

        Log::info('campaign ids in csv', $campaign_ids);

        if (empty($campaign_ids)) {
            return ['error' => 'No campaigns ready for CSV generation.'];
        }

        foreach ($campaign_ids as $uniq_campaign_id) {
            $existingCampaignShortUrl = CampaignShortUrl::where('campaign_id', $uniq_campaign_id)
                ->where('url_shortener', 'like', '%'.$urlShortenerName.'%')
                ->first();

            // if campaign short url not found in db
            if (!$existingCampaignShortUrl) {

                $alias_for_campaign = uniqid();
                $url_for_keitaro = $this->campaignService->generateUrlForCampaign($urlShortenerName, $alias_for_campaign);

                Log::debug('keitaro campaign id: ('. $uniq_campaign_id .
                    ') and url: ('. $urlShortenerName .
                    ') not found. Generated: ('.$url_for_keitaro.')'
                );

                // generate new campaign short url in db
                $newCampaignShortUrl = $this->campaignShortUrlRepository->create([
                    'campaign_id' => $uniq_campaign_id,
                    'url_shortener' => $url_for_keitaro,
                    'campaign_alias' => $alias_for_campaign,
                    'url_shortener_id' => $urlShortener->id,
                    'deleted_on_keitaro' => false
                ]);

                $campaign_short_urls_new[$newCampaignShortUrl->campaign_id] = $newCampaignShortUrl;
                $campaign_short_urls[$newCampaignShortUrl->campaign_id] = $newCampaignShortUrl;
            } else {
                $campaign_short_urls[$existingCampaignShortUrl->campaign_id] = $existingCampaignShortUrl;
            }
        }

        // create new campaigns on Keitaro
        $newCampaignsData = ['campaigns' => $campaign_short_urls_new, 'domain_id' => $domain_id];
        Log::info('New Keitaro Campaigns Generation starts with data: ', $newCampaignsData);
        dispatch(new CreateCampaignsOnKeitaro($newCampaignsData));

        // total count of available records
        $availableCount = $totalRecords > $requestCount ? $requestCount : $totalRecords;
        $numBatches = intval(ceil($availableCount / $batchSize));
        $batch_no = str_replace('.', '', microtime(true));

        Log::info('Request count: '. $requestCount .' ; Records available '. $availableCount);

        $filename = "/csv/byterevenue-messages-$batch_no.csv";

        $batch_file = BatchFile::create([
            'filename' => $filename,
            'path' => $filename, // duplicate of filename field, mb remove this <---
            'request_count' => $requestCount, // total records requested
            'number_of_entries' => $availableCount, // total available records for this condition
            'is_ready' => 0,
            'url_shortener_id' => $urlShortener->id,
            'campaign_ids' => $campaign_ids,
        ]);

        // original foreign link to campaigns (remove after change to campaign_ids method for all)
        $batch_file->campaigns()->attach($campaign_ids);

        // start generating sequence of jobs
        for ($batch = 0; $batch < $numBatches; $batch++) {
            $offset = $batch * $batchSize;
            $is_last = $batch >= $numBatches - 1;

            // if requested count for single job and less than batch size
            if ($is_last) {
                $cutSize = $requestCount % $batchSize;
            }

            // create params and dispatch the job
            $params = [
                'offset' => $offset, // using only for logging
                'batchSize' => isset($cutSize) && $cutSize > 0 ? $cutSize : $batchSize, // last records of uncompleted batch
                'url_shortener' => $urlShortenerName,
                'batch_no' => $batch_no,
                'batch_file' => $batch_file,
                'campaign_short_urls' => $campaign_short_urls,
                'is_last' => $is_last,
                'type' => $type,
                'campaigns_ids' => $campaign_ids,
            ];

            dispatch(new ProcessCsvQueueBatch($params));
        }

        // request for Campaigns page (ajax request)
        if ($needFullResponse) {
            $one = $batch_file->toArray();
            $_batch_no = $batch_file->getBatchFromFilename();
            $specs = $this->broadcastLogRepository->getTotalSentAndClicksByBatch($_batch_no);
            $one['total_entries'] = $specs['total'];
            $one['total_sent'] = $specs['total_sent'];
            $one['total_unsent'] = $specs['total'] - $specs['total_sent'];
            $one['total_clicked'] = $specs['total_clicked'];
            $one['created_at_ago'] = $batch_file->created_at->diffForHumans();

            return ['success' => $one];
        }

        return ['success' => true];
    }

    /**
     * @param array $data
     *
     * @return null|BatchFile
     */
    public function regenerateUnsent(array $data)
    {
        Log::alert('Request for CSV REgeneration (WEB) Starting process...', $data);

        $url = $data['url_shortener'];
        $url_shortener = UrlShortener::where('name', $url)->first();

        if (is_null($url_shortener)) {
            Log::error('Url shortener not found');

            return null;
        }

        $original_batch = BatchFile::where('is_ready', 1)->find($data['batch']);

        if (is_null($original_batch) || $original_batch->number_of_entries <= 0) {
            Log::error('Original batch not found or empty');

            return null;
        }

        preg_match('/byterevenue-[^\/]*-(.*?)\.csv/', $original_batch->filename, $matches);

        if ($matches[1] ?? null) {
            $original_batch_no = $matches[1];
        } else {
            Log::error('Original batch number not found on parsing filename');

            return null;
        }

        $domain_id = $url_shortener->asset_id;
        $batchSize = 1000; // ids scope for each job
        $unsent_logs = $this->broadcastLogRepository->getUnsent(['batch' => $original_batch_no]);
        $total = count($unsent_logs);
        $uniq_campaign_ids = $this->broadcastLogRepository->getUniqueCampaignsIDsFromExistingBatch($original_batch_no);
        $type = 'campaign' === $data['type'] ? 'campaign' : 'fifo';
        $type_id = null;
        $message_id = null;

        if ('campaign' === $data['type']) {
            $campaign_ids = $data['campaign_ids'];
            if (count($campaign_ids)) {
                $campaign = Campaign::find($campaign_ids[0]);
                if ($campaign->message->body != $data['message_body']) {
                    $new_message = $campaign->message->replicate();
                    $new_message->body = $data['message_body'];
                    $new_message->save();
                    $message_id = $new_message->id;
                }
            }

            $uniq_campaign_ids = $campaign_ids;
            $type_id = $campaign_ids;
            $type = 'campaign';
        }

        $numBatches = intval(ceil($total / $batchSize));
        $batch_no = $original_batch_no ."_1";
        $filename = "/csv/byterevenue-regen-$batch_no.csv";

        if ($numBatches == 0) {
            return null;
        }

        // todo:: maybe set status regen in original batch (temp busy)

        $batch_file = BatchFile::create([
            'filename'          => $filename,
            'path'              => $filename, // duplicated filename mb remove
            'number_of_entries' => $total,
            'is_ready'          => 0,
            'prev_batch_id'     => $original_batch->id,
            'campaign_ids'      => $uniq_campaign_ids,
            'url_shortener_id'  => $url_shortener->id,
        ]);

        $batch_file->campaigns()->attach($uniq_campaign_ids);

        for ($batchCnt = 0; $batchCnt < $numBatches; $batchCnt++) {
            $offset = $batchCnt * $batchSize;
            $is_last = $batchCnt >= $numBatches - 1;

            $params = [
                'offset' => $offset,
                'batchSize' => $batchSize,
                'url_shortener' => $url_shortener,
                'original_batch_no' => $original_batch_no,
                'batch_no' => $batch_no,
                'batch_file' => $batch_file,
                'is_last' => $is_last,
                'type' => $type,
                'type_id' => $type_id,
                'message_id' => $message_id
            ];

            dispatch(new ProcessCsvRegenQueueBatch($params));
        }

        $campaign_short_urls = $this->createCampaignShortUrls(
            $uniq_campaign_ids,
            $url,
            $url_shortener,
        );

        $params = ['campaigns' => $campaign_short_urls, 'domain_id' => $domain_id];

        dispatch(new CreateCampaignsOnKeitaro($params));

        $original_batch->update(['number_of_entries' => $original_batch->number_of_entries - $total]);

        return $batch_file;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function campaigns(array $data)
    {
        // get all campaigns which have messages ready to be sent
        $uniq_campaign_ids = $this->broadcastLogRepository->getUniqueCampaignsIDs();
        $user_id = $data['filter_client'] ?? null;

        $campaigns = isset($user_id)
            ? $this->campaignService->getUnsentByIdsOfUser($uniq_campaign_ids->toArray(), $user_id)
            : $this->campaignService->getUnsentByIds($uniq_campaign_ids);

        $urlShorteners = UrlShortener::onlyRegistered()->orderby('id', 'desc')->get();

        $params = [];
        $params['clients'] = User::all();
        $params['selected_client'] = $user_id;
        $queue_stats = $this->broadcastLogRepository->getQueueStats();
        $params['total_in_queue'] = $queue_stats['total_in_queue'];//BroadcastLog::select()->count();
        $params['campaigns'] = $campaigns;
        $params['files'] = [];
        $params['urlShorteners'] = $urlShorteners;
        $params['total_not_downloaded_in_queue'] = $queue_stats['total_not_downloaded_in_queue'];
        //BroadcastLog::select()->where('is_downloaded_as_csv', 0)->count();

        return $params;
    }
}
