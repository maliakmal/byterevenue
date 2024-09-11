<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BatchFile;
use App\Models\Campaign;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Trait\CSVReader;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BatchFileController extends Controller
{

    /**
     * @param CampaignRepositoryInterface $campaignRepository
     */
    public function __construct(
        protected CampaignRepositoryInterface $campaignRepository,
        protected BroadcastLogRepositoryInterface $broadcastLogRepository
    )
    {

    }


    public function getFormContentFromCampaign(Request $request){
        $campaign = $this->campaignRepository->find($request->campaign_id);
        $result = array();
        $result['data'] = $campaign->message;

        return response()->json($result);
    }

    public function checkStatus(Request $request){
        $file_ids = isset($_POST['files'])?$_POST['files']:[];
        $file_ids = is_array($file_ids)?$file_ids:[];
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


        $result = array();
        $result['data'] = $files;
        $result['ids'] = $request->files;

        return response()->json($result);
    }


    public function index(Request $request){

        $campaign_ids = $request->campaign_ids;
        $campaign_ids = is_array($campaign_ids)?$campaign_ids:[];
        $campaign_ids[] = 0;

        $campaigns = Campaign::select()->whereIn('id', $campaign_ids)->get();
        $message = null;
        if(count($campaigns) == 1){
            $message = $campaigns[0]->message;
        }
        $files = [];
        $result = ['data'=>['files'=>[], 'campaigns'=>$campaigns->toArray(), 'message'=>$message]];
        foreach($campaigns as $campaign){

            foreach($campaign->batchFiles()->orderby('id', 'desc')->get() as $file){
                $one = $file->toArray();
                $batch_no = $file->getBatchFromFilename();
                // get all entries with the campaig id and the batch no
                $specs = $this->broadcastLogRepository->getTotalSentAndClicksByCampaignAndBatch($campaign->id, $batch_no);
                $one['total_entries'] = $specs['total'];
                $one['total_sent'] =  $specs['total_sent'];
                $one['total_unsent'] = $specs['total'] - $specs['total_sent'];
                $one['total_clicked'] = $specs['total_clicked'];
                
                $one['created_at_ago'] = $file->created_at->diffForHumans();;

                $files[] = $one;
            }
        }
        $result['data']['files'] = $files;

        return response()->json($result);
    }

}
