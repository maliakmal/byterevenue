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
use App\Models\CampaignShortUrl;
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
        $request->validate([
            'number_messages' => ['required', 'integer', 'min:1', 'max:100000'],
            'url_shortener'   => ['required', 'string'],
            'type'            => ['required', 'string', 'in:campaign,fifo'],
            'campaign_ids'    => ['required_if:type,campaign', 'array'],
            'campaign_ids.*'  => ['required_if:type,campaign', 'integer'],
        ]);

        Log::alert('Request for CSV generation (WEB) Starting process...', $request->all());

        $requestCount = intval($request->number_messages); // count of records in CSV
        $urlShortenerName = trim($request->url_shortener);
        $urlShortener = UrlShortener::where('name', $urlShortenerName)->first();
        $domain_id = $urlShortener->asset_id;
        $campaign_short_urls = [];
        $campaign_short_urls_map = [];
        $batchSize = 1000; // ids scope for each job
        $type = 'campaign' === $request->type ? 'campaign' : 'fifo';

        // total count of batches for the job
        $ignored_campaigns = Campaign::select('id')->where('is_ignored_on_queue', true)->pluck('id')->toArray();
        // TODO:: is_ignored_on_queue - is blacklisted campaign? mb separate table?

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

        $allowedCompanyIds = array_values(array_diff($request->campaign_ids ?? [], $ignored_campaigns));
        $campaign_ids = 'campaign' === $type ?
            Campaign::whereIn('id', $allowedCompanyIds)->pluck('id')->toArray() :
            $this->broadcastLogRepository->getUniqueCampaignsIDs($requestCount, $ignored_campaigns);

        Log::info('campaign ids in csv', $campaign_ids);

        if (empty($campaign_ids)) {
            return redirect()->route('jobs.index')->with('error', 'No campaigns ready for CSV generation.');
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
                $existingCampaignShortUrl = $this->campaignShortUrlRepository->create([
                    'campaign_id' => $uniq_campaign_id,
                    'url_shortener' => $url_for_keitaro,
                    'campaign_alias' => $alias_for_campaign,
                    'url_shortener_id' => $urlShortener->id,
                    'deleted_on_keitaro' => false
                ]);
            }

            $campaign_short_urls[$existingCampaignShortUrl->campaign_id] = $existingCampaignShortUrl;
        }

        // create new campaigns on Keitaro
        $newCampaignsData = ['campaigns' => $campaign_short_urls, 'domain_id' => $domain_id];
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
                'campaign_short_urls' => $campaign_short_urls_map,
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
