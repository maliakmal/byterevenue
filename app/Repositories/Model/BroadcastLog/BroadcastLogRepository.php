<?php

namespace App\Repositories\Model\BroadcastLog;

use App\Models\BroadcastLog;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Model\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BroadcastLogRepository extends BaseRepository implements BroadcastLogRepositoryInterface
{
    public function __construct(BroadcastLog $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $ids
     * @param $fieldsToUpdate
     * @return mixed
     */
    public function updateWithIDs(array $ids, $fieldsToUpdate)
    {
        return $this->model->whereIn('id', $ids)->update($fieldsToUpdate);
    }

    /**
     * @param array $inputs
     * @return LengthAwarePaginator
     */
    public function paginateBroadcastLogs(array $inputs, bool $paginate)
    {
        $query = $this->model->newQuery();
        if(!empty($inputs['campaign_id'])){
            $query = $query->where('campaign_id', $inputs['campaign_id']);
        }
        if(!empty($inputs['status'])){
            $query = $query->where('status', $inputs['status']);
        }
        if(isset($inputs['is_click'])){
            $query = $query->where('is_click', $inputs['is_click']);
        }
        $query = $query->orderBy('id', 'DESC');
        if($paginate){
            $query = $query->with(['campaign.user']);
            return $query->paginate();
        }
        return $query->get();
    }
}
