<?php

namespace App\Services;

use App\Jobs\CreateCampaignsOnKeitaro;
use App\Jobs\ProcessCsvQueueBatch;
use App\Jobs\ProcessCsvQueueBatchByCampaigns;
use App\Jobs\ProcessCsvRegenQueueBatch;
use App\Models\BatchFile;
use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Models\UrlShortener;
use App\Models\User;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Model\CampaignShortUrl\CampaignShortUrlRepository;
use App\Services\Campaign\CampaignService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

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
    public function index(Request $request)
    {
        $download_me = null;
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        $urlShorteners = UrlShortener::withCount('campaignShortUrls')->onlyRegistered()->orderby('id', 'desc')->get();
        $files = BatchFile::with('urlShortener')->withCount('campaigns')->orderby($sortBy, $sortOrder)->paginate(15);

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
    public function createCampaignShortUrl($uniq_campaign_id, $urlShortenerName, $urlShortener)
    {
        $result = [
            // 'exists' => null,
            'new' => null,
        ];

        $result['exists'] = $this->campaignShortUrlRepository->findWithCampaignIDUrlID($uniq_campaign_id, $urlShortenerName);

        if (!$result['exists']) {
            $alias_for_campaign = uniqid();
            $url_for_keitaro = $this->campaignService->generateUrlForCampaign($urlShortenerName, $alias_for_campaign);

            Log::alert('GenerateService -> keitaro campaignShortUrl create: ', [
                'uniq_campaign_id: ' => $uniq_campaign_id,
                'urlShortenerName: ' => $urlShortenerName,
                'url_shortener'      => $urlShortener,
                'alias_for_campaign' => $alias_for_campaign,
                'url_for_keitaro'    => $url_for_keitaro,
            ]);

            $result['new'] = $this->campaignShortUrlRepository->create([
                'campaign_id'        => $uniq_campaign_id,
                'url_shortener'      => $url_for_keitaro,    // store reference to the short domain <-> campaign
                'campaign_alias'     => $alias_for_campaign,
                'url_shortener_id'   => $urlShortener->id,
                'deleted_on_keitaro' => false
            ]);
        }

        return $result;
    }

    public function processGenerate(array $params, $needFullResponse = null)
    {
        $requestCount      = intval($params['number_messages']); // count of records in CSV
        $urlShortenerName  = trim($params['url_shortener']);

        Log::alert('Request for CSV generation. Starting process...', $params);

        $urlShortener = UrlShortener::where('name', $urlShortenerName)->first();
        $domain_id    = $urlShortener->asset_id;
        $campaign_short_urls     = [];
        $campaign_short_urls_new = [];
        $batchSize = 1000; // ids scope for each job

        // total count of batches for the job
        $ignored_campaigns = Campaign::select('id')->where('is_ignored_on_queue', true)->pluck('id')->toArray();
        // TODO:: is_ignored_on_queue - is blacklisted campaign? mb separate table?

        $totalRecords = BroadcastLog::query()
            ->whereNotIn('campaign_id', $ignored_campaigns)
            ->whereNull('batch')
            ->count();

        if (0 == $totalRecords) {
            return ['error' => 'No messages ready for CSV generation.'];
        }

        $campaign_ids = $this->broadcastLogRepository->getUniqueCampaignsIDs($requestCount, $ignored_campaigns);

        Log::info('GenerateService -> campaign ids in csv', $campaign_ids);

        if (empty($campaign_ids)) {
            return ['error' => 'No campaigns ready for CSV generation.'];
        }

        foreach ($campaign_ids as $uniq_campaign_id) {
            $get_or_create_short = $this->createCampaignShortUrl($uniq_campaign_id, $urlShortenerName, $urlShortener);

            // if short is exists $new_generate_short == null, if created new record $new_generate_short == new CampaignShortUrl
            $campaign_short_urls_new[] = $get_or_create_short['new'] ?? null;
            $campaign_short_urls[] = $get_or_create_short['exists'] ?? $get_or_create_short['new'];
        }

        $campaign_short_urls_new = array_filter($campaign_short_urls_new);

        // create new campaigns on Keitaro
        if (!empty($campaign_short_urls_new)) {
            $newCampaignsData = ['campaigns' => $campaign_short_urls_new, 'domain_id' => $domain_id];
            Log::info('GenerateService -> New Keitaro Campaigns Generation starts with data: ', $newCampaignsData);
            dispatch(new CreateCampaignsOnKeitaro($newCampaignsData));
        }

        // total count of available records
        $availableCount = $totalRecords > $requestCount ? $requestCount : $totalRecords;
        $numBatches = intval(ceil($availableCount / $batchSize));
        $batch_no = str_replace('.', '', microtime(true));

        Log::info('GenerateService -> Request count: '. $requestCount .' ; Records available '. $availableCount);

        $filename = "/csv/byterevenue-messages-$batch_no.csv";

        $batch_file = BatchFile::create([
            'filename' => $filename,
            'path' => $filename, // duplicate of filename field, mb remove this <---
            'request_count' => $requestCount, // total records requested
            'number_of_entries' => $availableCount, // total available records for this condition
            'is_ready' => 0,
            'url_shortener_id' => $urlShortener->id,
            'campaign_ids' => $campaign_ids,
            'type' => 'fifo',
        ]);

        // original foreign link to campaigns (remove after change to campaign_ids method for all)
        // $batch_file->campaigns()->attach($campaign_ids);

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
                'campaign_short_urls' => collect($campaign_short_urls),
                'is_last' => $is_last,
                'type' => 'fifo',
                'campaign_ids' => $campaign_ids,
            ];

            dispatch(new ProcessCsvQueueBatch($params));
        }

        return ['success' => true];
    }

    public function processGenerateByCampaigns(array $params)
    {
        $requestCount     = intval($params['number_messages']); // count of records in CSV
        $urlShortenerName = trim($params['url_shortener']);
        $campaign_ids     = $params['campaign_ids'] ?? [];
        $campaigns_data   = [];

        Log::alert('Request for CSV generation. Starting process...', $params);

        $urlShortener = UrlShortener::where('name', $urlShortenerName)->first();
        $domain_id    = $urlShortener->asset_id;
        $campaign_short_urls     = [];
        $campaign_short_urls_new = [];
        $batchSize = 1000; // ids scope for each job

        // total count of batches for the job
        $ignored_campaigns = Campaign::select('id')->where('is_ignored_on_queue', true)->pluck('id')->toArray();
        // TODO:: is_ignored_on_queue - is blacklisted campaign? mb separate table?

        $allowedCompanyIds = array_values(array_diff($campaign_ids, $ignored_campaigns));
        $campaign_ids = Campaign::whereIn('id', $allowedCompanyIds)->pluck('id')->toArray();

        Log::info('GenerateService -> campaign ids in csv', $campaign_ids);

        if (empty($campaign_ids)) {
            return ['error' => 'No campaigns ready for CSV generation.'];
        }

        $totalRecords = 0;
        $campaigns_count = [];
        $campaigns_models = [];
        foreach ($campaign_ids as $uniq_campaign_id) {
            $get_or_create_short = $this->createCampaignShortUrl($uniq_campaign_id, $urlShortenerName, $urlShortener);

            // if short is exists $new_generate_short == null, if created new record $new_generate_short == new CampaignShortUrl
            $campaign_short_urls_new[] = $get_or_create_short['new'] ?? null;
            $campaign_short_urls[] = $get_or_create_short['exists'] ?? $get_or_create_short['new'];
            $campaigns_models[$uniq_campaign_id] = Campaign::findOrFail($uniq_campaign_id);
            $campaigns_count[$uniq_campaign_id] = BroadcastLog::query()
                ->where('campaign_id', $uniq_campaign_id)
                ->whereNull('batch')
                ->count();
            $totalRecords += $campaigns_count[$uniq_campaign_id];
        }

        if (0 == $totalRecords) {
            return ['error' => 'No messages ready for CSV generation.'];
        }

        $campaign_short_urls_new = array_filter($campaign_short_urls_new);

        // create new campaigns on Keitaro
        if (!empty($campaign_short_urls_new)) {
            $newCampaignsData = ['campaigns' => $campaign_short_urls_new, 'domain_id' => $domain_id];
            Log::info('GenerateService -> New Keitaro Campaigns Generation starts with data: ', $newCampaignsData);
            dispatch(new CreateCampaignsOnKeitaro($newCampaignsData));
        }

        // total count of available records
        $availableCount = $totalRecords > $requestCount ? $requestCount : $totalRecords;
        $batch_no = str_replace('.', '', microtime(true));

        Log::info('GenerateService -> Request count: '. $requestCount .' ; Records available '. $availableCount);

        $filename = "/csv/byterevenue-messages-$batch_no.csv";

        $batch_file = BatchFile::create([
            'filename' => $filename,
            'path' => $filename, // duplicate of filename field, mb remove this <---
            'request_count' => $requestCount, // total records requested
            'number_of_entries' => $availableCount, // total available records for this condition
            'is_ready' => 0,
            'url_shortener_id' => $urlShortener->id,
            'campaign_ids' => $campaign_ids,
            'type' => 'campaign',
        ]);

        // original foreign link to campaigns (remove after change to campaign_ids method for all)
        // $batch_file->campaigns()->attach($campaign_ids);

        arsort($campaigns_data);
        $part = ceil($availableCount / count($campaign_ids));
        $part = $part < $batchSize ? $part : $batchSize;

        while ($availableCount <= 0) {
            foreach ($campaigns_count as $campaign_id => $records_count) {
                $chunk = $part > $campaigns_count[$campaign_id] ? $campaigns_count[$campaign_id] : $part;

                if ($chunk <= 0 || $availableCount <=0) continue;

                $params = [
                    'batchSize' => $chunk,
                    'url_shortener' => $urlShortenerName,
                    'batch_no' => $batch_no,
                    'batch_file' => $batch_file,
                    'campaign_short_urls' => collect($campaign_short_urls),
                    'is_last' => $availableCount - $chunk <= 0,
                    'campaign_ids' => [$campaign_id],
                    'remainder' => $availableCount,
                ];

                $campaigns_count[$campaign_id] -= $chunk;
                $availableCount -= $chunk;

                dispatch(new ProcessCsvQueueBatchByCampaigns($params));
            }
        }

        $one = $batch_file->toArray();
        $_batch_no = $batch_file->getBatchFromFilename();
        $specs = $this->broadcastLogRepository->getTotalSentAndClicksByBatch($_batch_no);
        $one['total_entries'] = $specs['total'];
        $one['total_sent'] = $specs['total_sent'];
        $one['total_unsent'] = $specs['total'] - $specs['total_sent'];
        $one['total_clicked'] = $specs['total_clicked'];
        $one['created_at_ago'] = $batch_file->created_at->diffForHumans();

        return ['success' => true];
    }

    /**
     * @param array $data
     *
     * @return null|BatchFile
     */
    public function regenerateUnsent(array $data)
    {
        Log::alert('Request for CSV REgeneration. Starting process...', $data);

        $campaign_short_urls = [];
        $campaign_short_urls_new = [];

        $urlShortenerName = trim($data['url_shortener']);
        $urlShortener = UrlShortener::where('name', $urlShortenerName)->first();

        if (is_null($urlShortener)) {
            Log::error('REgenerateService -> Url shortener not found');

            return null;
        }

        $domain_id = $urlShortener->asset_id;
        $original_batch = BatchFile::where('is_ready', 1)->find($data['batch']);

        if (is_null($original_batch) || $original_batch->number_of_entries <= 0) {
            Log::error('REgenerateService -> Original batch not found or empty');

            return null;
        }

        preg_match('/byterevenue-[^\/]*-(.*?)\.csv/', $original_batch->filename, $matches);

        if ($matches[1] ?? null) {
            $original_batch_no = $matches[1];
        } else {
            Log::error('REgenerateService -> Original batch number not found on parsing filename');

            return null;
        }

        $batchSize = 1000; // ids scope for each job
        $unsent_logs = BroadcastLog::query()
            ->where('is_sent', 0)
            //->where('is_downloaded_as_csv', 0) // maybe this instead of is_sent
            ->where('batch', $original_batch_no)
            ->get();

        $total = count($unsent_logs);

        $campaign_ids = $this->broadcastLogRepository->getUniqueCampaignsIDsFromExistingBatch($original_batch_no);

        foreach ($campaign_ids as $uniq_campaign_id) {
            $get_or_create_short = $this->createCampaignShortUrl($uniq_campaign_id, $urlShortenerName, $urlShortener);

            // if short is exists $new_generate_short == null, if created new record $new_generate_short == new CampaignShortUrl
            $campaign_short_urls_new[] = $get_or_create_short['new'] ?? null;
            $campaign_short_urls[] = $get_or_create_short['exists'] ?? $get_or_create_short['new'];
        }

        $campaign_short_urls_new = array_filter($campaign_short_urls_new);

        // create new campaigns on Keitaro
        if (!empty($campaign_short_urls_new)) {
            $newCampaignsData = ['campaigns' => $campaign_short_urls_new, 'domain_id' => $domain_id];
            Log::info('GenerateService -> New Keitaro Campaigns Generation starts with data: ', $newCampaignsData);
            dispatch(new CreateCampaignsOnKeitaro($newCampaignsData));
        }

        $numBatches = intval(ceil($total / $batchSize));
        $batch_no = $original_batch_no . "_1";
        $filename = "/csv/byterevenue-regen-$batch_no.csv";

        if ($numBatches == 0) {
            return null;
        }

        // todo:: maybe set status regen in original batch

        $batch_file = BatchFile::create([
            'filename' => $filename,
            'path' => $filename, // duplicated filename mb remove
            'number_of_entries' => $total,
            'is_ready'          => 0,
            'prev_batch_id'     => $original_batch->id,
            'campaign_ids'      => $campaign_ids,
            'url_shortener_id'  => $urlShortener->id,
            'type'              => 'regen',
        ]);

        $batch_file->campaigns()->attach($campaign_ids);

        for ($batchCnt = 0; $batchCnt < $numBatches; $batchCnt++) {
            $offset = $batchCnt * $batchSize;
            $is_last = $batchCnt >= $numBatches - 1;

            $params = [
                'offset' => $offset,
                'batchSize' => $batchSize,
                'url_shortener' => $urlShortener,
                'original_batch_no' => $original_batch_no,
                'original_batch' => $original_batch,
                'batch_no' => $batch_no,
                'batch_file' => $batch_file,
                'is_last' => $is_last,
                'campaign_short_urls' => collect($campaign_short_urls),
            ];

            dispatch(new ProcessCsvRegenQueueBatch($params));
        }

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
