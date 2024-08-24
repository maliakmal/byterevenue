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




    /**
     * @param array $inputs
     */

     public function requeueUnsent(array $inputs){
        $query = $this->model->newQuery();
        $query = $query->where('is_sent', '0');

        if(!empty($inputs['campaign_id'])){
            $query = $query->where('campaign_id', $inputs['campaign_id']);
        }

        if(!empty($inputs['batch'])){
            $query = $query->where('batch', $inputs['batch']);
        }

        if(!empty($inputs['count'])){
            $query = $query->limit($input['count']);
        }



     }

     /**
      * @params array $inputs
      * @return mixed
      */

     public function getUnsent(array $inputs){
        $query = $this->model->newQuery();
        $query = $query->where('is_sent', '0');

        if(!empty($inputs['campaign_id'])){
            $query = $query->where('campaign_id', $inputs['campaign_id']);
        }

        if(!empty($inputs['batch'])){
            $query = $query->where('batch', $inputs['batch']);
        }

        if(!empty($inputs['count'])){
            $query = $query->limit($input['count']);
        }

        return $query->get();

     }


     public function getUniqueCampaignsIDsFromExistingBatch($batch){
        $query = $this->model->newQuery()->select('campaign_id')->distinct();
        $query = $query->where('batch', $batch);
        return $query->pluck('campaign_id');
     }


     public function getUniqueCampaignsIDs($limit){
        $query = $this->model->newQuery()->select('campaign_id')->distinct();
        $query = $query->whereNull('batch');

        $query = $query->limit($limit);

        return $query->pluck('campaign_id');
     }
}
