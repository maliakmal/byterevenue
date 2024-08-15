<?php

namespace App\Repositories\Model\Campaign;

use App\Models\Campaign;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Repositories\Model\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CampaignRepository extends BaseRepository implements CampaignRepositoryInterface
{
    public function __construct(Campaign $model)
    {
        $this->model = $model;
    }

    /**
     * @param int $userID
     * @return mixed
     */
    public function getCampaignsForUser(int $userID)
    {
        return $this->model->where('user_id', $userID)->get();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function reportCampaigns(array $inputs)
    {
        $query = $this->model->newQuery();
        $query = $query->with(['user'])->withCount(['broadCaseLogMessages', 'broadCaseLogMessagesSent', 'broadCaseLogMessagesUnSent', 'broadCaseLogMessagesClick', 'broadCaseLogMessagesNotClick']);
        if(!empty($inputs['user_id'])){
            $query = $query->where('user_id', $inputs['user_id']);
        }
        $query = $query->orderBy('id', 'DESC');
        return $query->paginate();
    }
}
