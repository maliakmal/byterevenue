<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Repositories\Contract\User\UserRepositoryInterface;
use App\Services\Report\ReportService;
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
        private ReportService $reportService
    ) {}

    public function messages()
    {
        $download_csv = request()->get('download_csv') == 1;
        $inputs = request()->all();
        $inputs['per_page'] = $download_csv ? null : request('per_page', 15);
        $userId = request()->get('user_id');

        $list = $this->broadcastLogRepository->paginateBroadcastLogs($inputs);
        $users = $this->userRepository->all();
        $campaigns = [];

        if (isset($userId)) {
            $campaigns = $this->campaignRepository->getCampaignsForUser($userId);
        }

        if ($download_csv) {
            $response = Response::make($this->collectionToCSV($list), 200);
            unset($inputs['download_csv']);
            $filteredInputs = array_filter($inputs, function ($value) {
                return !is_null($value);
            });
            $baseFilename = 'report';

            if (!empty($filteredInputs)) {
                $filtersString = implode('-', array_map(
                    function ($key, $value) {
                        return "$key-$value";
                    },
                    array_keys($filteredInputs),
                    $filteredInputs
                ));
                $baseFilename .= "-$filtersString";
            }

            $_filename = $baseFilename . '-' . time() . '.csv';
            $response->header('Content-Type', 'text/csv')
                ->header('Content-disposition', "attachment; filename=$_filename");

            return $response;
        }

        return view('reports.messages', compact('list', 'users', 'campaigns'));
    }

    /**
     * @return Application|Factory|View|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function campaigns()
    {
        $download_csv = request()->get('download_csv') == 1;
        $columns = [
            'id',
            'title',
            'user_id'
        ];
        $inputs = request()->all();
        $inputs['per_page'] = $download_csv ? null : request('per_page', 15);
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

        if ($download_csv) {
            $response = Response::make($this->collectionToCSV($list, ['user']), 200);
            $response->header('Content-Type', 'text/csv')
                ->header('Content-disposition', 'attachment; filename="report-campaign-' . time() . '.csv"');

            return $response;
        }

        return view('reports.campaigns', compact('list', 'users'));
    }
}
