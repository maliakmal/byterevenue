<?php

namespace App\Services;

use App\Jobs\CreateCampaignsOnKeitaro;
use App\Jobs\ProcessCsvRegenQueueBatch;
use App\Models\BatchFile;
use App\Models\Campaign;
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
     * @return array
     */
    public function getJobs(array $filters = [])
    {
        $download_me = null;

        $urlShorteners = UrlShortener::withCount('campaignShortUrls')
            ->onlyRegistered()
            ->orderBy('id', 'desc')
            ->get();

        $filesQuery = BatchFile::with('urlShortener')
            ->withCount('campaigns')
            ->orderBy('id', 'desc');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $filesQuery->where(function($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('prev_batch_id', 'like', "%{$search}%")
                    ->orWhere('filename', 'like', "%{$search}%")
                    ->orWhere('path', 'like', "%{$search}%")
                    ->orWhere('number_of_entries', 'like', "%{$search}%")
                    ->orWhere('is_ready', 'like', "%{$search}%")
                    ->orWhere('url_shortener_id', 'like', "%{$search}%")
                    ->orWhere('created_at', 'like', "%{$search}%")
                    ->orWhere('updated_at', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['shortDomain'])) {
            $filesQuery->where('url_shortener_id', $filters['shortDomain']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $status = $filters['status'];

            if ($status === 'completed') {
                $filesQuery->where('is_ready', true)
                    ->where('number_of_entries', '>', 0);
            } elseif ($status === 'regenerated') {
                $filesQuery->where('is_ready', true)
                    ->where('number_of_entries', 0);
            }
        }

        $perPage = isset($filters['per_page']) ? (int)$filters['per_page'] : 5;
        $files = $filesQuery->paginate($perPage);

        $queue_stats = $this->broadcastLogRepository->getQueueStats();
        $params = [
            'total_in_queue' => $queue_stats['total_in_queue'],
            'files' => $files,
            'download_me' => $download_me,
            'urlShorteners' => $urlShorteners,
            'total_not_downloaded_in_queue' => $queue_stats['total_not_downloaded_in_queue'],
        ];

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

    /**
     * @param array $data
     *
     * @return bool|BatchFile
     */
    public function regenerateUnsent(array $data)
    {
        // get all unsent
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

        $url_shortener = $data['url_shortener'];
        $_url_shortener = UrlShortener::where('name', $url_shortener)->first();

        if (is_null($_url_shortener)) {
            Log::error('Url shortener not found');

            return null;
        }

        $domain_id = $_url_shortener->asset_id;
        $batchSize = 1000; // ids scope for each job
        $unsent_logs = $this->broadcastLogRepository->getUnsent(['batch' => $original_batch_no]);
        $total = count($unsent_logs);
        $uniq_campaign_ids = $this->broadcastLogRepository->getUniqueCampaignsIDsFromExistingBatch($original_batch_no);
        $type = 'fifo';
        $type_id = null;
        $message_id = null;

        if ('campaign' === ($data['type'] ?? '')) {
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

        $campaign_short_urls = $this->createCampaignShortUrls(
            $uniq_campaign_ids,
            $url_shortener,
            $_url_shortener
        );

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
            'url_shortener_id'  => $_url_shortener->id,
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
