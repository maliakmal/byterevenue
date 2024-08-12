<?php

namespace App\Http\Controllers;

use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Repositories\Contract\User\UserRepositoryInterface;
use App\Trait\CSVReader;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    use CSVReader;
    public function __construct(
        protected BroadcastLogRepositoryInterface $broadcastLogRepository,
        protected UserRepositoryInterface $userRepository,
        protected CampaignRepositoryInterface $campaignRepository,
    )
    {
    }

    public function messages()
    {
        $filter = array(
            'sortby'=> request('sortby')?request('sortby'):'id_desc',
            'count'=> request('count')?request('count'):15,
        );
        $download_csv = request()->get('download_csv') == 1;
        $inputs = request()->all();
        
        $list = $this->broadcastLogRepository->paginateBroadcastLogs($inputs, !$download_csv);
        $users = $this->userRepository->all();
        $campaigns = [];
        if(request()->exists('user_id') && (request()->get('user_id')!=null)){
            $campaigns = $this->campaignRepository->getCampaignsForUser(request()->get('user_id'));
        }
        if($download_csv){
            $response = Response::make($this->collectionToCSV($list), 200);
            unset($inputs['download_csv']);
            $_filename =  implode('-', array_map(function($key, $value) {
                return $key . '-' . $value;
            }, array_keys($inputs), $inputs));
            $response->header('Content-Type', 'text/csv')
                    ->header('Content-disposition','attachment; filename="report-'.$_filename.'-'.time().'.csv"');;
            return $response;
        }
        return view('reports.messages', compact('list', 'filter', 'users', 'campaigns'));
    }
}
