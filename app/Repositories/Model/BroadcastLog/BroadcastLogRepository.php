<?php

namespace App\Repositories\Model\BroadcastLog;

use App\Models\BroadcastLog;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Model\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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
     * @return LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function paginateBroadcastLogs(array $inputs)
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
        if(isset($inputs['per_page'])){
            $query = $query->with(['campaign.user']);
            return $query->paginate($inputs['per_page']);
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
            $query = $query->limit($inputs['count']);
        }



     }

     /**
      * @params array $inputs
      * @return mixed
      */

     public function getUnsent(array $inputs){
        $query = $this->model->newQuery()->join('campaigns', 'broadcast_logs.campaign_id', '=', 'campaigns.id')
                                                ->where('campaigns.is_ignored_on_queue', '=', 0);
        $query = $query->where('is_sent', '0');

        if(!empty($inputs['campaign_id'])){
            $query = $query->where('campaign_id', $inputs['campaign_id']);
        }

        if(!empty($inputs['batch'])){
            $query = $query->where('batch', $inputs['batch']);
        }

        if(!empty($inputs['count'])){
            $query = $query->limit($inputs['count']);
        }

        return $query->get();

     }


     public function getUniqueCampaignsIDsFromExistingBatch($batch){
        $query = $this->model->newQuery()->select('campaign_id')->distinct();
        $query = $query->where('batch', $batch);
        return $query->pluck('campaign_id');
     }


     public function getUniqueCampaignsIDs($limit = null){
        $query = $this->model->newQuery()->select('campaign_id');
        //$query = $query->whereNull('batch');
        if(!is_null($limit)){
            $query = $query->take($limit);
        }

        return $query->groupby('campaign_id')->pluck('campaign_id')->values();
     }


     public function getQueueStats(){
        $result = [];

        $result['total_in_queue'] = $this->model->join('campaigns', 'broadcast_logs.campaign_id', '=', 'campaigns.id')
                                                ->where('campaigns.is_ignored_on_queue', '=', 0)->count();
        $result['total_not_downloaded_in_queue'] = $this->model->join('campaigns', 'broadcast_logs.campaign_id', '=', 'campaigns.id')
                                                ->where('campaigns.is_ignored_on_queue', '=', 0)->where('is_downloaded_as_csv', 0)->count();
        return $result;
    }

    public function getTotalSentAndClicksByCampaign($campaign_id){
        $totals = $this->model->newQuery()->where('campaign_id', $campaign_id)->select(
            [
                DB::raw('COUNT(id) as total'),
                DB::raw('COUNT(CASE WHEN batch IS NOT NULL THEN 1 END) as total_processed'),
                DB::raw('COUNT(CASE WHEN is_sent = true THEN 1 END) as total_sent'),
                DB::raw('COUNT(CASE WHEN is_click = true THEN 1 END) as total_clicked')
            ]
        )->first();

        return $totals;
     }
     public function getTotalSentAndClicksByBatch($batch_no){
        $totals = $this->model->newQuery()->where('batch', $batch_no)->select(
            [
                DB::raw('COUNT(id) as total'),
                DB::raw('COUNT(CASE WHEN batch IS NOT NULL THEN 1 END) as total_processed'),
                DB::raw('COUNT(CASE WHEN is_sent = true THEN 1 END) as total_sent'),
                DB::raw('COUNT(CASE WHEN is_click = true THEN 1 END) as total_clicked')
            ]
        )->first();

        return $totals;
     }
     public function getTotalSentAndClicksByCampaignAndBatch($campaign_id, $batch_no){
        $totals = $this->model->newQuery()->where('campaign_id', $campaign_id)->where('batch', $batch_no)->select(
            [
                DB::raw('COUNT(id) as total'),
                DB::raw('COUNT(CASE WHEN is_sent = true THEN 1 END) as total_sent'),
                DB::raw('COUNT(CASE WHEN is_click = true THEN 1 END) as total_clicked')
            ]
        )->first();

        return $totals;
     }

}
