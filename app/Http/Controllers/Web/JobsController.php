<?php

namespace App\Http\Controllers\Web;

use App\Enums\BroadcastLog\BroadcastLogStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CampaignsGetRequest;
use App\Http\Requests\JobRegenerateRequest;
use App\Jobs\CreateCampaignsOnKeitaro;
use App\Jobs\ProcessCsvQueueBatch;
use App\Jobs\ProcessCsvRegenQueueBatch;
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
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobsController extends Controller
{
    public function __construct(
        protected CampaignShortUrlRepositoryInterface $campaignShortUrlRepository,
        protected CampaignService $campaignService,
        protected BroadcastLogRepositoryInterface $broadcastLogRepository,
        protected JobService $jobService
    ) {}

    /**
     * @param CampaignsGetRequest $request
     *
     * @return View
     */
    public function campaigns(CampaignsGetRequest $request)
    {
        $params = $this->jobService->campaigns($request->validated());

        return view('jobs.campaigns', compact('params'));
    }

    public function index()
    {
        $params = $this->jobService->index();

        if (request()->wantsJson()) {
            return $this->responseSuccess($params);
        }

        return view('jobs.index', compact('params'));
    }

    // start process of generating CSV (web-side)
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
        $campaign_short_urls_new = [];
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
        if ($request->ajax()) {
            $one = $batch_file->toArray();
            $_batch_no = $batch_file->getBatchFromFilename();
            // get all entries with the campaig id and the batch no
            $specs = $this->broadcastLogRepository->getTotalSentAndClicksByBatch($_batch_no);
            $one['total_entries'] = $specs['total'];
            $one['total_sent'] = $specs['total_sent'];
            $one['total_unsent'] = $specs['total'] - $specs['total_sent'];
            $one['total_clicked'] = $specs['total_clicked'];
            $one['created_at_ago'] = $batch_file->created_at->diffForHumans();

            return response()->json(['data' => $one]);
        }

        return redirect()->route('jobs.index')->with('success', 'CSV is being generated.');
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
            return redirect()->route('jobs.index')->with('error', 'CSV generation failed.');
        }

        return redirect()->route('jobs.index')->with('success', 'CSV is being generated.');
    }

    public function downloadFile($filename)
    {
        $batch = BatchFile::find($filename);
        $response = new StreamedResponse(function () use ($batch) {
            $handle = fopen('php://output', 'w');
            // Output the column headings
            fputcsv($handle, ['UID', 'Phone', 'Subject', 'Text']);

            $batch_no = $batch->getBatchFromFilename();

            // Query and write data to the file
            $rows = BroadcastLog::select()->where('batch', '=', $batch_no)->orderby('id', 'ASC')->cursor();
            foreach ($rows as $row) {
                fputcsv($handle, [
                    trim($row->slug),
                    trim($row->recipient_phone),
                    '',
                    trim($row->message_body),
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '.csv"');

        return $response;
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
            return response()->error('not found');
        }

        if (
            !$this->broadcastLogRepository->updateByModel([
                'sent_at' => Carbon::now(),
                'is_sent' => true,
                'status' => BroadcastLogStatus::SENT,
            ], $model)
        ) {
            return response()->error('update failed');
        }

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
            return response()->error('not found');
        }

        if (
            !$this->broadcastLogRepository->updateByModel([
                'is_click' => true,
                'clicked_at' => Carbon::now(),
            ], $model)
        ) {
            return response()->error('update failed');
        }

        return response()->success();
    }
}
