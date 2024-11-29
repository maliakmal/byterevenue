<?php

namespace App\Http\Controllers\Api;

use App\Enums\BroadcastLog\BroadcastLogStatus;
use App\Http\Controllers\ApiController;
use App\Http\Requests\JobRegenerateRequest;
use App\Jobs\CreateCampaignsOnKeitaro;
use App\Jobs\ProcessCsvQueueBatch;
use App\Models\BatchFile;
use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Models\UrlShortener;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\CampaignShortUrl\CampaignShortUrlRepositoryInterface;
use App\Services\Campaign\CampaignService;
use App\Services\JobService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JobsApiController extends ApiController
{
    public function __construct(
        protected CampaignShortUrlRepositoryInterface $campaignShortUrlRepository,
        protected CampaignService $campaignService,
        protected BroadcastLogRepositoryInterface $broadcastLogRepository,
        protected JobService $jobService
    ) {}

    public function fifo()
    {
        $params = $this->jobService->index();

        return $this->responseSuccess($params);
    }

    public function postIndex(Request $request)
    {
        $total = $request->number_messages ?? 100; // count of records in CSV
        $urlShortenerName = $request->url_shortener;
        $urlShortener = UrlShortener::where('name', $urlShortenerName)->first();
        $domain_id = $urlShortener->asset_id;
        $campaign_short_urls = [];
        $batchSize = 1000; // ids scope for each job
        $type = 'campaign' === $request->type ? 'campaign' : 'fifo';

        // total count of batches for the job
        $ignored_campaigns = Campaign::select('id')->where('is_ignored_on_queue', true)->pluck('id')->toArray();
        $totalRecords = BroadcastLog::query()
            ->whereNotIn('campaign_id', $ignored_campaigns)
            ->whereNull('batch')
            ->when('campaign' === $request->type, function ($query) use ($request) {
                $query->whereIn('campaign_id', $request->campaign_ids);
            })
            ->count();

        if (0 == $totalRecords) {
            redirect()->route('jobs.index')->with('error', 'No messages ready for CSV generation.');
        }

        $sourceCampaignsIds = 'campaign' === $type ?
            $request->campaign_ids :
            $this->broadcastLogRepository->getUniqueCampaignsIDs($total, $ignored_campaigns);

        $campaign_ids = array_filter($sourceCampaignsIds, fn($value) => !empty($value));
        Log::info('campaign ids in csv', $campaign_ids);

        foreach ($campaign_ids as $uniq_campaign_id) {

            $existingCampaignShortUrl = $this->campaignShortUrlRepository->findWithCampaignIDUrlID($uniq_campaign_id, $urlShortenerName);
            Log::info('keitaro campaign for campaign id ('.$uniq_campaign_id.') and url ('.$urlShortenerName.') =>');
            Log::info($existingCampaignShortUrl);

            if (!$existingCampaignShortUrl) {
                Log::info('keitaro campaign for campaign id ('.$uniq_campaign_id.') and url ('.$urlShortenerName.') not found - generating');

                $alias_for_campaign = uniqid();
                $url_for_keitaro = $this->campaignService->generateUrlForCampaign($urlShortenerName, $alias_for_campaign);
                Log::info('keitaro campaign redirect url generated ('.$url_for_keitaro.')');

                $campaign_short_urls[] = $this->campaignShortUrlRepository->create([
                    'campaign_id' => $uniq_campaign_id,
                    'url_shortener' => $url_for_keitaro,
                    'campaign_alias' => $alias_for_campaign,
                    'url_shortener_id' => $urlShortener->id,
                    'deleted_on_keitaro' => false
                ]);
            } else {
                $campaign_short_urls[] = $existingCampaignShortUrl;
            }
        }

        Log::info('JobController > Campaign Short Urls', $campaign_short_urls);
        Log::info('JobController > Domain ID: ' . $domain_id);


        $params = ['campaigns' => $campaign_short_urls, 'domain_id' => $domain_id];
        Log::info('New Keitaro Campaigns Generation starts here', $params);
        dispatch(new CreateCampaignsOnKeitaro($params));
        Log::info('New Keitaro Campaigns Generation dispatched');

        $campaign_short_urls_map = [];
        foreach($campaign_short_urls as $one){
            $campaign_short_urls_map[$one->campaign_id] = $one;
        }

        $baseCount = $totalRecords > $total ? $total : $totalRecords;
        $numBatches = ceil($baseCount / $batchSize);
        $batch_no = str_replace('.', '', microtime(true));

        Log::info('Number of records requested '. $total.' - Number of records available '.$baseCount);

        $filename = "/csv/byterevenue-messages-$batch_no.csv";

        $batch_file = BatchFile::create([
            'filename' => $filename,
            'path' => /*env('DO_SPACES_ENDPOINT') .*/ $filename,
            'number_of_entries' => $total,
            'is_ready' => 0,
            'url_shortener_id' => $urlShortener->id,
        ]);

        $batch_file->campaigns()->attach($campaign_ids);

        for ($batch = 0; $batch < $numBatches; $batch++) {
            $offset = $batch * $batchSize;
            $is_last = $batch >= $numBatches - 1;

            Log::info('BATCH number - '. $batch .' total - '. $numBatches);

            $params = [
                'offset' => $offset,
                'batchSize' => $batchSize,
                'url_shortener' => $urlShortenerName,
                'batch_no' => $batch_no,
                'batch_file' => $batch_file,
                'campaign_short_urls'=>$campaign_short_urls_map,
                'is_last' => $is_last,
                'type' => $type,
                'campaigns_ids' => $campaign_ids,
            ];

            dispatch(new ProcessCsvQueueBatch($params));
        }

        Log::info('ProcessCSV Jobs dispatched');

        $one = $batch_file->toArray();
        $_batch_no = $batch_file->getBatchFromFilename();
        // get all entries with the campaig id and the batch no
        $specs = $this->broadcastLogRepository->getTotalSentAndClicksByBatch($_batch_no);
        $one['total_entries'] = $specs['total'];
        $one['total_sent'] = $specs['total_sent'];
        $one['total_unsent'] = $specs['total'] - $specs['total_sent'];
        $one['total_clicked'] = $specs['total_clicked'];
        $one['created_at_ago'] = $batch_file->created_at->diffForHumans();

        return $this->responseSuccess($one);
    }

    /**
     * @param JobRegenerateRequest $request
     *
     * @return JsonResponse|RedirectResponse
     */
    public function regenerateUnsent(JobRegenerateRequest $request)
    {
        $batch_file = $this->jobService->regenerateUnsent($request->validated());

        if (!$batch_file) {
            return $this->responseError(message: 'CSV generation failed.');
        }

        return $this->responseSuccess($batch_file);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSentMessage(Request $request)
    {
        $request->validate([
            'u' => ['required', 'string', 'size:8'],
        ]);

        $uid = $request->u;
        $model = $this->broadcastLogRepository->findBy('slug', $uid);

        if (!$model) {
            // answer for outside services
            return response()->error('not found');
        }

        if (
            !$this->broadcastLogRepository->updateByModel([
                'sent_at' => Carbon::now(),
                'is_sent' => true,
                'status' => BroadcastLogStatus::SENT,
            ], $model)
        ) {
            // answer for outside services
            return response()->error('update failed');
        }
        // answer for outside services
        return response()->success();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateClickMessage(Request $request)
    {
        $request->validate([
            'u' => ['required', 'string', 'size:8'],
        ]);

        $uid = $request->u;
        $model = $this->broadcastLogRepository->findBy('slug', $uid);

        if (!$model) {
            // answer for outside services
            return response()->error('not found');
        }

        if (
            !$this->broadcastLogRepository->updateByModel([
                'is_click' => true,
                'clicked_at' => Carbon::now(),
            ], $model)
        ) {
            // answer for outside services
            return response()->error('update failed');
        }
        // answer for outside services
        return response()->success();
    }
}
