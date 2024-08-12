<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Repositories\Contract\User\UserRepositoryInterface;

class ReportController extends Controller
{
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
        $inputs = request()->all();
        $list = $this->broadcastLogRepository->paginateBroadcastLogs($inputs);
        $users = $this->userRepository->all();
        $campaigns = [];
        if(request()->exists('user_id') && (request()->get('user_id')!=null)){
            $campaigns = $this->campaignRepository->getCampaignsForUser(request()->get('user_id'));
        }
        return view('reports.messages', compact('list', 'filter', 'users', 'campaigns'));
    }

}
