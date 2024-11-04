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

    public function index(Request $request)
    {

        $download_me = null;
        $urlShorteners = UrlShortener::onlyRegistered()->orderby('id', 'desc')->get();

        if ($request->isMethod('post')) {
            $unique_campaigns = collect();
            $unique_campaign_map = [];

            $total = $request->number_messages;
            $urlShortenerName = $request->url_shortener;
            $urlShortener = UrlShortener::where('name', $urlShortenerName)->first();
            $domain_id = $urlShortener->asset_id;
            $campaign_short_urls = [];
            $batchSize = 100;
            $type = 'fifo';
            $type_id = null;

            if ($request->type == 'campaign') {

                $uniq_campaign_ids = array_filter($request->campaign_ids, function ($value) {
                    return $value !== 0 && !empty($value);
                });

                foreach ($uniq_campaign_ids as $uniq_campaign_id):

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
                endforeach;
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

                foreach ($uniq_campaign_ids as $uniq_campaign_id):
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

                endforeach;

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


        $directory = storage_path('app/csv/');
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
        $params['total_not_downloaded_in_queue'] = $queue_stats['total_not_downloaded_in_queue'];// BroadcastLog::select()->where('is_downloaded_as_csv', 0)->count();
        return view('jobs.index', compact('params'));
    }

    /**
     * @return JsonResponse
     */
    public function indexApi()
    {
        return $this->responseSuccess($this->jobService->index());
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
            return $this->responseError('CSV generation failed.');
        }

        if ($request->ajax()) {
            return response()->json(['data' => $batch_file]);
        } else {
            return redirect()->route('jobs.index')->with('success', 'CSV is being generated.');
        }

    }

    /**
     * @param JobRegenerateRequest $request
     *
     * @return JsonResponse
     */
    public function regenerateUnsentApi(JobRegenerateRequest $request)
    {
        $batch_file = $this->jobService->regenerateUnsent($request->validated());

        if ($batch_file) {
            return $this->responseSuccess($batch_file, 'CSV is being generated.');
        }

        return $this->responseError('CSV generation failed.');
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
                    $row->id,
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

    public function updateSentMessage(Request $request)
    {
        $request->validate(['uid' => 'required|numeric|min:1']);
        $uid = $request->uid;
        $model = $this->broadcastLogRepository->find($uid);
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

    public function updateClickMessage(Request $request)
    {
        $request->validate(['uid' => 'required|numeric|min:1']);
        $uid = $request->uid;
        $model = $this->broadcastLogRepository->find($uid);
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
