<?php

namespace App\Http\Controllers;

use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Repositories\Contract\User\UserRepositoryInterface;
use App\Trait\CSVReader;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    use CSVReader;
    public function __construct(
        protected BroadcastLogRepositoryInterface $broadcastLogRepository,
        protected UserRepositoryInterface $userRepository,
        protected CampaignRepositoryInterface $campaignRepository,
    ) {
    }

    public function messages()
    {
        $filter = [
            'sortby' => request('sortby', 'id_desc'),
            'count' => request('count', 15),
        ];
        $download_csv = request()->get('download_csv') == 1;
        $inputs = request()->all();
        $userId = request()->get('user_id');

        $list = $this->broadcastLogRepository->paginateBroadcastLogs($inputs, !$download_csv);
        $users = $this->userRepository->all();
        $campaigns = [];
        if (isset($userId)) {
            $campaigns = $this->campaignRepository->getCampaignsForUser($userId);
        }
        if ($download_csv) {
            $response = Response::make($this->collectionToCSV($list), 200);
            unset($inputs['download_csv']);
            $_filename = implode('-', array_map(function ($key, $value) {
                return $key . '-' . $value;
            }, array_keys($inputs), $inputs));
            $response->header('Content-Type', 'text/csv')
                ->header('Content-disposition', 'attachment; filename="report-' . $_filename . '-' . time() . '.csv"');
            ;
            return $response;
        }
        return view('reports.messages', compact('list', 'filter', 'users', 'campaigns'));
    }

    /**
     * @return Application|Factory|View|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function campaigns()
    {
        $filter = [
            'sortby' => request('sortby', 'id_desc'),
            'count' => request('count', 15),
        ];
        $download_csv = request()->get('download_csv') == 1;
        $columns = [
            'id',
            'title',
            'user_id'
        ];
        $list = $this->campaignRepository->reportCampaigns(request()->all(), $columns, !$download_csv);
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
        if ($download_csv) {
            $response = Response::make($this->collectionToCSV($list, ['user']), 200);
            $response->header('Content-Type', 'text/csv')
                ->header('Content-disposition', 'attachment; filename="report-campaign-' . time() . '.csv"');
            ;
            return $response;
        }
        return view('reports.campaigns', compact('list', 'filter', 'users'));
    }
}
