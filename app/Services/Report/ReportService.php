<?php

namespace App\Services\Report;

use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Repositories\Contract\User\UserRepositoryInterface;
use App\Trait\CSVReader;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;

class ReportService
{
    use CSVReader;
    public function __construct(
        protected BroadcastLogRepositoryInterface $broadcastLogRepository,
        protected UserRepositoryInterface $userRepository,
        protected CampaignRepositoryInterface $campaignRepository,
    ) {
    }

    public function getMessages(Request $request)
    {
        $inputs = $request->all();
        $userId = $request->user_id;
        $inputs['per_page'] = request('per_page', 15);
        $list = $this->broadcastLogRepository->paginateBroadcastLogs($inputs);
        $users = $this->userRepository->all();
        $campaigns = [];
        if (isset($userId)) {
            $campaigns = $this->campaignRepository->getCampaignsForUser($userId);
        }
        return compact('list', 'users', 'campaigns');
    }

    public function downloadMessagesCSV(Request $request)
    {
        $inputs = $request->all();
        $list = $this->broadcastLogRepository->paginateBroadcastLogs($inputs);
        $csvContent = $this->collectionToCSV($list);
        return $csvContent;
    }

    public function getCampaigns(Request $request)
    {
        $columns = [
            'id',
            'title',
            'user_id'
        ];
        $inputs = $request->all();
        $inputs['per_page'] = request('per_page', 15);
        $list = $this->campaignRepository->reportCampaigns($inputs, $columns);
        foreach ($list as $index => $item) {
            $list[$index]->user_name = '';
            if (!empty($item->user)) {
                $list[$index]->user_name = $item->user->name;
            }
            $list[$index]->ctr = 0;
            if ($item->broad_case_log_messages_sent_count > 0) {
                $list[$index]->ctr = ($item->broad_case_log_messages_click_count / $item->broad_case_log_messages_sent_count) * 100;
            }
        }
        $users = $this->userRepository->all();
        return compact('list', 'users');
    }
    public function downloadCampaignsCSV(Request $request)
    {
        $inputs = $request->all();

        $columns = [
            'id',
            'title',
            'user_id'
        ];

        $list = $this->campaignRepository->reportCampaigns($inputs, $columns);
        foreach ($list as $index => $item) {
            $list[$index]->user_name = '';
            if (!empty($item->user)) {
                $list[$index]->user_name = $item->user->name;
            }
            $list[$index]->ctr = 0;
            if ($item->broad_case_log_messages_sent_count > 0) {
                $list[$index]->ctr = ($item->broad_case_log_messages_click_count / $item->broad_case_log_messages_sent_count) * 100;
            }
        }
        $csvContent = $this->collectionToCSV($list, ['user']);

        return $csvContent;
    }
}
