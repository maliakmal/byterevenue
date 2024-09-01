<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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


    public function index(Request $request){

        $campaign = $this->campaignRepository->find($request->campaign_id);
        $_campaign = $campaign->toArray();
        $_campaign['username'] = $campaign->user->name; 
        $result = ['data'=>['files'=>[], 'campaign'=>$_campaign, 'message'=>$campaign->message]];
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

            $result['data']['files'][] = $one;
        }

        return response()->json($result);;
    }

}
