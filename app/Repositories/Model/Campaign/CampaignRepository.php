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

    public function getUnsentByIdsOfUser(array $ids, $user_id = null)
    {
        $ids = is_array($ids) ? $ids : [];
        $ids[] = 0; // hack in case someone passes an empty array

        return $this->model->whereIn('id', $ids)->where('user_id', $user_id)->whereIn('status', [Campaign::STATUS_PROCESSING])->get();
    }


    public function getUnsentByIds(array $ids)
    {
        $ids = is_array($ids) ? $ids : [];
        $ids[] = 0; // hack in case someone passes an empty array

        return $this->model->whereIn('id', $ids)->whereIn('status', [Campaign::STATUS_PROCESSING])->get();

    }


    public function getPendingCampaigns(array $params)
    {
        $fiveDaysAgo = Carbon::now()->subDays(35);
        return $this->model->whereIn('status', [Campaign::STATUS_PROCESSING, Campaign::STATUS_DONE])->where('submitted_at', '>=', $fiveDaysAgo)->get();
    }

    /**
     * @param array $inputs
     * @param array $selectColumns
     * @param bool $paginate
     * @return LengthAwarePaginator|Builder[]|Collection
     */
    public function reportCampaigns(array $inputs, array $selectColumns = [])
    {
        $query = $this->model->newQuery();
        if (!empty($selectColumns)) {
            $query = $query->select($selectColumns);
        }
        $query = $query->with(['user'])->withCount(['broadCaseLogMessages', 'broadCaseLogMessagesSent', 'broadCaseLogMessagesUnSent', 'broadCaseLogMessagesClick', 'broadCaseLogMessagesNotClick']);
        if (!empty($inputs['user_id'])) {
            $query = $query->where('user_id', $inputs['user_id']);
        }
        $query = $query->orderBy('id', 'DESC');
        if (isset($inputs['per_page'])) {
            return $query->paginate($inputs['per_page']);
        }
        return $query->get();
    }

    public function markCampaignAsDone($campaign_id)
    {
        $campaign = $this->model->find($campaign_id);
        $campaign->status = Campaign::STATUS_DONE;
        $campaign->save();
    }

    /**
     * @param array $filter
     *
     * @return LengthAwarePaginator
     */
    public function getFiltered(array $filter)
    {
        $campaigns = $this->model->newQuery()->with(['recipient_list', 'user', 'message']);

        if (!is_null($filter['status'])) {
            $campaigns->where('status', $filter['status']);
        }

        if (auth()->user()->hasRole('admin')) {

            if (!empty($filter['user_id'])) {
                $campaigns->where('user_id', $filter['user_id']);
            }

        } else {
            $campaigns->where('user_id', auth()->id());
        }

        if (!empty($filter['search'])) {
            $campaigns->where('title', 'like', '%' . $filter['search'] . '%')
                ->orWhere('id', $filter['search']);
        }

        $campaigns->orderBy($filter['sort_by'], $filter['sort_order']);

        return $campaigns->paginate($filter['per_page']);
    }
}
