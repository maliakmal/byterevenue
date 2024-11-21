<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\BatchFile;
use App\Models\Campaign;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BatchFileApiController extends ApiController
{
    /**
     * @param CampaignRepositoryInterface $campaignRepository
     */
    public function __construct(
        protected CampaignRepositoryInterface $campaignRepository,
        protected BroadcastLogRepositoryInterface $broadcastLogRepository
    ) {}

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getFormContentFromCampaign(Request $request){
        $campaign = $this->campaignRepository->find($request->campaign_id);
        $result = $campaign->message;

        return $this->responseSuccess(options: $result);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function checkStatus(Request $request){
        $file_ids = isset($_POST['files']) ? $_POST['files'] : [];
        $file_ids = is_array($file_ids) ? $file_ids : [];
        $file_ids[] = 0;
        $files = [];

        foreach(BatchFile::select()->whereIn('id', $file_ids)->where('is_ready', 1)->get() as $file){
            $one = $file->toArray();
            $batch_no = $file->getBatchFromFilename();
            // get all entries with the campaig id and the batch no
            $specs = $this->broadcastLogRepository->getTotalSentAndClicksByBatch($batch_no);
            $one['total_entries'] = $specs['total'];
            $one['total_sent'] =  $specs['total_sent'];
            $one['total_unsent'] = $specs['total'] - $specs['total_sent'];
            $one['total_clicked'] = $specs['total_clicked'];
            $one['created_at_ago'] = $file->created_at->diffForHumans();;
            $files[] = $one;
        }

        return $this->responseSuccess(data: $files, options: ['ids' => $request->files]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $campaign_ids = $request->campaign_ids;
        $campaign_ids = is_array($campaign_ids) ? $campaign_ids : [];

        $campaigns = Campaign::whereIn('id', $campaign_ids)->get();
        $message = null;

        if (count($campaigns)) {
            $message = $campaigns[0]->message;
        }

        $files = [];
        $result = [
            'campaigns' => $campaigns->toArray(),
            'message' => $message,
        ];

        foreach($campaigns as $campaign) {

            foreach($campaign->batchFiles()->orderby('id', 'desc')->get() as $file){
                $one = $file->toArray();
                $batch_no = $file->getBatchFromFilename();
                // get all entries with the campaig id and the batch no
                $specs = $this->broadcastLogRepository->getTotalSentAndClicksByCampaignAndBatch($campaign->id, $batch_no);
                $one['total_entries'] = $specs['total'];
                $one['total_sent'] = $specs['total_sent'];
                $one['total_unsent'] = $specs['total'] - $specs['total_sent'];
                $one['total_clicked'] = $specs['total_clicked'];
                $one['created_at_ago'] = $file->created_at->diffForHumans();;
                $files[] = $one;
            }
        }

        $result['files'] = $files;

        return $this->responseSuccess($result);
    }
}
