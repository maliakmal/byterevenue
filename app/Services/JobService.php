<?php

namespace App\Services;

use App\Jobs\CreateCampaignsOnKeitaro;
use App\Jobs\ProcessCsvQueueBatch;
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
        $urlShorteners = UrlShortener::onlyRegistered()->orderby('id', 'desc')->get();

        $files = BatchFile::orderby('id', 'desc')->paginate(15);

        $batches = [];
        // get individual batches
        foreach ($files as $_file) {
            $batches[] = $_file->getBatchFromFilename();
        }

        $message_ids = BroadcastLog::whereIn('batch', $batches)->distinct()->pluck('message_id');

        // get count of all messages in the queue
        $queue_stats = $this->broadcastLogRepository->getQueueStats();
        $params['total_in_queue'] = $queue_stats['total_in_queue'];//BroadcastLog::select()->count();
        $params['files'] = $files;
        $params['download_me'] = $download_me;
        $params['urlShorteners'] = $urlShorteners;
        $params['total_not_downloaded_in_queue'] = $queue_stats['total_not_downloaded_in_queue'];

        return $params;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function createJob(array $data)
    {
        $total = $data['number_messages'];
        $urlShortenerName = $data['url_shortener'];
        $urlShortener = UrlShortener::where('name', $urlShortenerName)->first();
        $domain_id = $urlShortener->asset_id;
        $campaign_short_urls = [];
        $batchSize = 100;
        $type = 'fifo';
        $type_id = null;

        if ($data['type'] === 'campaign') {
            $uniq_campaign_ids = array_filter($data['campaign_ids'], function ($value) {
                return $value !== 0 && !empty($value);
            });

            $campaign_short_urls = $this->createCampaignShortUrls(
                $uniq_campaign_ids,
                $urlShortenerName,
                $urlShortener
            );

            $type = 'campaign';
            $type_id = $uniq_campaign_ids;
            //$uniq_campaign_ids = [$uniq_campaign_id];

        } else {
            $uniq_campaign_ids = array_filter(
                $this->broadcastLogRepository->getUniqueCampaignsIDs($total)->toArray(),
                function ($value) {
                    return $value !== 0 && !empty($value);
                }
            );

            $campaign_short_urls = $this->createCampaignShortUrls(
                $uniq_campaign_ids,
                $urlShortenerName,
                $urlShortener
            );
        }

        $numBatches = ceil($total / $batchSize);
        $campaign_short_url_map = []; // maps campaign_id -> short url
        $batch_no = preg_replace("/[^A-Za-z0-9]/", '', microtime());

        $filename = "/csv/byterevenue-messages-$batch_no.csv";

        $batch_file = BatchFile::create([
            'filename' => $filename,
            'path' => env('DO_SPACES_ENDPOINT') . $filename,
            'number_of_entries' => $total,
            'is_ready' => 0
        ]);

        $batch_file->campaigns()->attach($uniq_campaign_ids);

        Log::info('numBatches - ' . $numBatches);
        for ($batch = 0; $batch < $numBatches; $batch++) {
            $offset = $batch * $batchSize;
            $is_last = $batch == ($numBatches - 1) ? true : false;
            Log::info('BATCH number - ' . $batch);
            Log::info('BATCH number - ' . $numBatches);
            $params = [];
            $params['offset'] = $offset;
            $params['batchSize'] = $batchSize;
            $params['url_shortener'] = $urlShortenerName;
            $params['batch_no'] = $batch_no;
            $params['batch_file'] = $batch_file;
            $params['is_last'] = $is_last;
            $params['type'] = $type;
            $params['type_id'] = $type_id;
            dispatch(new ProcessCsvQueueBatch($params));//$offset, $batchSize, $urlShortenerName, $batch_no, $batch_file, $is_last, $type, $type_id));

        }

        $params = ['campaigns' => $campaign_short_urls, 'domain_id' => $domain_id];
        dispatch(new CreateCampaignsOnKeitaro($params));

        $one = $batch_file->toArray();
        $_batch_no = $batch_file->getBatchFromFilename();
        // get all entries with the campaig id and the batch no
        $specs = $this->broadcastLogRepository->getTotalSentAndClicksByBatch($_batch_no);
        $one['total_entries'] = $specs['total'];
        $one['total_sent'] = $specs['total_sent'];
        $one['total_unsent'] = $specs['total'] - $specs['total_sent'];
        $one['total_clicked'] = $specs['total_clicked'];
        $one['created_at_ago'] = $batch_file->created_at->diffForHumans();

        return $one;
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
        $batch_id = $data['batch'];
        $_batch = BatchFile::find($batch_id);
        preg_match('/byterevenue-[^\/]*-(.*?)\.csv/', $_batch->filename, $matches);
        if (!$matches[1]) {
            return false;
        } else {
            $batch = $matches[1];
        }
        $url_shortener = $data['url_shortener'];
        $_url_shortener = UrlShortener::where('name', $url_shortener)->first();
        $domain_id = $_url_shortener->asset_id;
        $original_batch_no = $batch;
        $campaign_short_urls = [];
        $batchSize = 100;
        $unsent_logs = $this->broadcastLogRepository->getUnsent(['batch' => $batch]);
        $total = count($unsent_logs);
        $uniq_campaign_ids = $this->broadcastLogRepository->getUniqueCampaignsIDsFromExistingBatch($batch);
        $type = 'fifo';
        $type_id = null;
        $message_id = null;

        if ($data['type'] == 'campaign') {
            $campaign_ids = $data['campaign_ids'];
            if (count($campaign_ids) == 1) {
                $campaign = Campaign::find($campaign_ids[0]);
                if ($campaign->message->body != $data['message_body']) {
                    $new_message = $campaign->message->replicate();
                    $new_message->body = $data['message_body'];
                    $new_message->save();
                    $message_id = $new_message->id;
                }
            }

            $uniq_campaign_ids = $campaign_ids;

            $campaign_short_urls = $this->createCampaignShortUrls(
                $uniq_campaign_ids,
                $url_shortener,
                $_url_shortener
            );

            $type = 'campaign';
            $type_id = $uniq_campaign_ids;

        } else {
            $campaign_short_urls = $this->createCampaignShortUrls(
                $uniq_campaign_ids,
                $url_shortener,
                $_url_shortener
            );
        }

        $numBatches = ceil($total / $batchSize);
        $campaign_short_url_map = []; // maps campaign_id -> short url
        $batch_no = "$batch _1";
        $filename = "/csv/byterevenue-regen-$batch_no.csv";

        $batch_file = BatchFile::create([
            'filename' => $filename,
            'path' => env('DO_SPACES_ENDPOINT') . $filename,
            'number_of_entries' => $total,
            'is_ready' => 0
        ]);
        $batch_file->campaigns()->attach($uniq_campaign_ids);

        Log::info("numBatches - $numBatches");
        for ($batch = 0; $batch < $numBatches; $batch++) {
            $offset = $batch * $batchSize;
            $is_last = $batch == ($numBatches + 1) ? true : false;
            Log::info('BATCH number - ' . $batch);
            dispatch(new ProcessCsvRegenQueueBatch($offset, $batchSize, $url_shortener, $original_batch_no, $batch_no, $batch_file, $is_last, $type, $type_id, $message_id));
        }

        $params = ['campaigns' => $campaign_short_urls, 'domain_id' => $domain_id];
        dispatch(new CreateCampaignsOnKeitaro($params));
        $original_filename = "/csv/byterevenue-messages-$batch.csv";

        $original_batch_file = BatchFile::select()->where('filename', $original_filename)->get()->first();
        if ($original_batch_file) {
            $original_batch_file->number_of_entries -= $total;
            $original_batch_file->save();
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
            : $this->campaignService->getUnsentByIds($uniq_campaign_ids->toArray());

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
