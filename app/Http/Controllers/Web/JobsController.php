<?php

namespace App\Http\Controllers\Web;

use App\Enums\BroadcastLog\BroadcastLogStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CampaignsGetRequest;
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

    public function index(Request $request)
    {
        $params = $this->jobService->index($request);

        if (request()->wantsJson()) {
            return $this->responseSuccess($params);
        }

        return view('jobs.index', compact('params'));
    }

    // start process of generating CSV (web-side)
    public function generateCsv(Request $request)
    {
        $params = $request->validate([
            'number_messages' => ['required', 'integer', 'min:1', 'max:100000'],
            'url_shortener'   => ['required', 'string'],
            'type'            => ['required', 'string', 'in:fifo'],
        ]);

        $result = $this->jobService->processGenerate($params, needFullResponse: $request->ajax());

        if ($result['error'] ?? null) {
            return redirect()->route('jobs.index')->with('error', $result['error']);
        }

        if ($request->ajax()) {
            return response()->json(['data' => $result['success'] ?? []]);
        }

        return redirect()->route('jobs.index')->with('success', 'CSV is being generated.');
    }

    // start process of generating CSV by Campaigns (web-side)
    public function generateCsvByCampaigns(Request $request)
    {
        $params = $request->validate([
            'number_messages' => ['required', 'integer', 'min:1', 'max:100000'],
            'url_shortener'   => ['required', 'string'],
            'type'            => ['required', 'string', 'in:campaign'],
            'campaign_ids'    => ['required', 'array'],
            'campaign_ids.*'  => ['required', 'integer'],
        ]);

        $result = $this->jobService->processGenerate($params);

        if ($result['error'] ?? null) {
            return redirect()->route('jobs.index')->with('error', $result['error']);
        }

        if ($request->ajax()) {
            return response()->json(['data' => $result['success'] ?? []]);
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
