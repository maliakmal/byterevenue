<?php

namespace App\Repositories\Model\Campaign;

use App\Models\Campaign;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Repositories\Model\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

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

    public function getUnsentByIdsOfUser(array $ids, $user_id = null){
        $ids = is_array($ids)?$ids:[];
        $ids[] = 0; // hack in case someone passes an empty array

        return $this->model->whereIn('id', $ids)->where('user_id', $user_id)->whereIn('status', [Campaign::STATUS_PROCESSING])->get();
    }


    public function getUnsentByIds(array $ids){
        $ids = is_array($ids)?$ids:[];
        $ids[] = 0; // hack in case someone passes an empty array

        return $this->model->whereIn('id', $ids)->whereIn('status', [Campaign::STATUS_PROCESSING])->get();

    }


    public function getPendingCampaigns(array $params){
        $fiveDaysAgo = Carbon::now()->subDays(35);
        return $this->model->whereIn('status', [Campaign::STATUS_PROCESSING, Campaign::STATUS_DONE])->where('submitted_at', '>=', $fiveDaysAgo)->get();
    }

    /**
     * @param array $inputs
     * @param array $selectColumns
     * @param bool $paginate
     * @return LengthAwarePaginator|Builder[]|Collection
     */
    public function reportCampaigns(array $inputs, array $selectColumns = [], bool $paginate = true)
    {
        $query = $this->model->newQuery();
        if(!empty($selectColumns)){
            $query = $query->select($selectColumns);
        }
        $query = $query->with(['user'])->withCount(['broadCaseLogMessages', 'broadCaseLogMessagesSent', 'broadCaseLogMessagesUnSent', 'broadCaseLogMessagesClick', 'broadCaseLogMessagesNotClick']);
        if(!empty($inputs['user_id'])){
            $query = $query->where('user_id', $inputs['user_id']);
        }
        $query = $query->orderBy('id', 'DESC');
        if($paginate){
            return $query->paginate();
        }
        return $query->get();
    }

    public function markCampaignAsDone($campaign_id){
        $campaign = $this->model->find($campaign_id);
        $campaign->status = Campaign::STATUS_DONE;
        $campaign->save();
    }
}
