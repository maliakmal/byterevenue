<?php

namespace App\Http\Controllers;

use App\Enums\BroadcastLog\BroadcastLogStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\CampaignsGetRequest;
use App\Http\Requests\JobRegenerateRequest;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Repositories\Contract\CampaignShortUrl\CampaignShortUrlRepositoryInterface;
use App\Services\Campaign\CampaignService;
use App\Jobs\ProcessCsvQueueBatch;
use App\Jobs\ProcessCsvRegenQueueBatch;
use App\Jobs\CreateCampaignsOnKeitaro;
use App\Services\JobService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Models\BatchFile;
use App\Models\UrlShortener;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class JobsController extends ApiController
{
    public function __construct(
        protected CampaignShortUrlRepositoryInterface $campaignShortUrlRepository,
        protected CampaignService $campaignService,
        protected BroadcastLogRepositoryInterface $broadcastLogRepository,
        protected JobService $jobService
    ) {
    }

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

    /**
     * @return JsonResponse
     */
    public function campaignsApi(CampaignsGetRequest $request)
    {
        return $this->responseSuccess($this->jobService->campaigns($request->validated()));
    }

    /**
     * @OA\Get(
     *     path="/jobs",
     *     summary="Get a list of jobs",
     *     tags={"Jobs"},
     *     @OA\Response(
     *         response=200,
     *         description="List of jobs",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     )
     * )
     * @return JsonResponse
     */
    public function index()
    {
        $params = $this->jobService->index();

        if (request()->wantsJson()) {
            return $this->responseSuccess($params);
        }

        return view('jobs.index', compact('params'));
    }

    public function postIndex(Request $request)
    {
        $total = $request->number_messages ?? 100; // count of records in CSV
        $urlShortenerName = $request->url_shortener;
        $urlShortener = UrlShortener::where('name', $urlShortenerName)->first();
        $domain_id = $urlShortener->asset_id;
        $campaign_short_urls = [];
        $batchSize = 100; // ids scope for each job
        $type = 'campaign' === $request->type ? 'campaign' : 'fifo';

        // total count of batches for the job
        $ignored_campaigns = Campaign::select('id')->where('is_ignored_on_queue', true)->get()->pluck('id');
        $totalRecords = BroadcastLog::query()
            ->whereNotIn('campaign_id', $ignored_campaigns)
            ->whereNull('batch')
            ->when('campaign' === $request->type, function ($query) use ($request) {
                $query->whereIn('campaign_id', $request->campaign_ids);
            })
            ->count();

        if (0 == $totalRecords) {
            return $request->ajax() ?
                response()->json(['error' => 'No messages ready for CSV generation.']) :
                redirect()->route('jobs.index')->with('error', 'No messages ready for CSV generation.');
        }

        $sourceCampaignsIds = 'campaign' === $type ?
        $request->campaign_ids :
        $this->broadcastLogRepository->getUniqueCampaignsIDs($total)->toArray();

        $campaign_ids = array_filter($sourceCampaignsIds, fn($value) => !empty($value));

        foreach ($campaign_ids as $uniq_campaign_id) {
            if (!$this->campaignShortUrlRepository->findWithCampaignIDUrlID($uniq_campaign_id, $urlShortenerName)) {
                $alias_for_campaign = uniqid();
                $url_for_keitaro = $this->campaignService->generateUrlForCampaign($urlShortenerName, $alias_for_campaign);

                $campaign_short_urls[] = $this->campaignShortUrlRepository->create([
                    'campaign_id' => $uniq_campaign_id,
                    'url_shortener' => $url_for_keitaro,    // store reference to the short domain <-> campaign
                    'campaign_alias' => $alias_for_campaign,
                    'url_shortener_id' => $urlShortener->id,
                    'deleted_on_keitaro' => false
                ]);
            }
        }

        $baseCount = $totalRecords > $total ? $total : $totalRecords;
        $numBatches = ceil($baseCount / $batchSize);
        $batch_no = str_replace('.', '', microtime(true));

        $filename = "/csv/byterevenue-messages-$batch_no.csv";

        $batch_file = BatchFile::create([
            'filename' => $filename,
            'path' => env('DO_SPACES_ENDPOINT') . $filename,
            'number_of_entries' => $total,
            'is_ready' => 0
        ]);

        $batch_file->campaigns()->attach($campaign_ids);

        for ($batch = 0; $batch < $numBatches; $batch++) {
            \Log::debug('BATCH number - '. $batch .' total - '. $numBatches);
            $offset = $batch * $batchSize;
            $is_last = $batch >= $numBatches - 1;

            Log::info('BATCH number - '. $batch .' total - '. $numBatches);

            $params = [
                'offset' => $offset,
                'batchSize' => $batchSize,
                'url_shortener' => $urlShortenerName,
                'batch_no' => $batch_no,
                'batch_file' => $batch_file,
                'is_last' => $is_last,
                'type' => $type,
                'campaigns_ids' => $campaign_ids,
            ];

            dispatch(new ProcessCsvQueueBatch($params));
        }

        $params = ['campaigns' => $campaign_short_urls, 'domain_id' => $domain_id];

        dispatch(new CreateCampaignsOnKeitaro($params));

        // from Process by Campaigns page
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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createJobApi(Request $request)
    {
        $request->validate([
            'number_messages' => 'required|numeric|min:1',
            'url_shortener' => 'required|string',
            'type' => 'required|string',
            'campaign_ids' => 'array|required_if:type,campaign',
        ]);

        return $this->responseSuccess($this->jobService->createJob($request->all()));
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
            if ($request->ajax()) {
                return $this->responseError('CSV generation failed.');
            }

            return redirect()->route('jobs.index')->with('error', 'CSV generation failed.');
        }

        if ($request->ajax()) {
            return response()->json(['data' => $batch_file]);
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
                    $row->slug,
                    $row->recipient_phone,
                    '',
                    $row->message_body,
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '.csv"');

        return $response;
    }

    public function download(Request $request)
    {
        // if user posted to select n non downloaded messages
        $limit = $request->limit;
        $Limit = $limit > 0 ? $limit : 100;
        $shortener = $request->shortener;
        $messages = BroadcastLog::where('is_downloaded_as_csv', 0)->take($limit)->get();

        BroadcastLog::where('id', '<=', BroadcastLog::where('is_downloaded_as_csv', 0)->take($limit)->get()->last()->id)
            ->update(['is_downloaded_as_csv' => 1]);
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
