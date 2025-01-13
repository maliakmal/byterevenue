<?php

namespace App\Repositories\Model\BroadcastLog;

use App\Models\BroadcastLog;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Model\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BroadcastLogRepository extends BaseRepository implements BroadcastLogRepositoryInterface
{
    /**
     * @param BroadcastLog $model
     */
    public function __construct(BroadcastLog $model)
    {
        parent::__construct($model);
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

    public function updateBySlug($slug, $fieldsToUpdate)
    {
        return $this->model->where('slug', '=', $slug)->update($fieldsToUpdate);
    }

    /**
     * @param array $inputs
     * @return LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function paginateBroadcastLogs(array $inputs)
    {
        $query = $this->model->newQuery();

        if (!empty($inputs['campaign_id'])) {
            $query = $query->where('campaign_id', $inputs['campaign_id']);
        }
        if (!empty($inputs['status'])) {
            $query = $query->where('status', $inputs['status']);
        }
        if (isset($inputs['is_click'])) {
            $query = $query->where('is_click', $inputs['is_click']);
        }
        $query = $query->orderBy('id', 'DESC');
        if (isset($inputs['per_page'])) {
            $query = $query->with(['campaign.user']);
            return $query->paginate($inputs['per_page']);
        }

        return $query->get();
    }

    /**
     * @params array $inputs
     * @return mixed
     */
    public function getUnsent()
    {
        return \DB::connection('mysql')
            ->table('broadcast_logs')
            ->whereNull('batch')
            ->get();
    }

    public function getUnsentCount()
    {
        return \DB::connection('mysql')
            ->table('broadcast_logs')
            ->whereNull('batch')
            ->count();
    }

    public function getUnsentCountByUserIds(array $userIds)
    {
        return \DB::connection('mysql')
            ->table('broadcast_logs')
            ->whereIn('user_id', $userIds)
            ->whereNull('batch')
            ->count();
    }

    public function getUnsentCountByCampaignIds(array $campaignIds)
    {
        return \DB::connection('mysql')
            ->table('broadcast_logs')
            ->whereIn('campaign_id', $campaignIds)
            ->whereNull('batch')
            ->count();
    }

    public function getUniqueCampaignsIDsFromExistingBatch($batch) {
        $query = \DB::connection('mysql')
            ->table('broadcast_logs')
            ->select('campaign_id')
            ->distinct();

        $query = $query->where('batch', $batch);

        return $query->pluck('campaign_id')->toArray();
    }

    public function getUniqueCampaignsIDs(?int $limit = null, ?array $ignored_campaigns = null): array
    {
        $subquery = \DB::table('broadcast_logs')
            ->select('campaign_id')
            ->whereNull('batch')
            ->when(!!$limit, function ($query) use ($limit) {
                return $query->take($limit);
            })
            ->when(!!$ignored_campaigns, function ($query) use ($ignored_campaigns) {
                return $query->whereNotIn('campaign_id', $ignored_campaigns);
            });

        $ids = \DB::table(\DB::raw("({$subquery->toSql()}) as limited"))
            ->select('campaign_id')
            ->distinct()
            ->pluck('campaign_id');

        return $ids->toArray();
    }

    public function getTotalSentAndClicksByCampaign($campaign_id)
    {
        $mainDb = \DB::connection('mysql')
            ->table('broadcast_logs')
            ->where('campaign_id', $campaign_id)
            ->select([
                DB::raw('COUNT(id) as total'),
                DB::raw('COUNT(CASE WHEN batch IS NOT NULL THEN 1 END) as total_processed'),
                DB::raw('COUNT(CASE WHEN is_sent = true THEN 1 END) as total_sent'),
                DB::raw('COUNT(CASE WHEN is_click = true THEN 1 END) as total_clicked')
            ]);

        $sums = (array)$mainDb->first();

        $slaveDb = \DB::connection('storage_mysql')
            ->table('broadcast_storage_master')
            ->where('campaign_id', $campaign_id)
            ->select([
                DB::raw('COUNT(id) as total'),
                DB::raw('COUNT(CASE WHEN sent_at IS NOT NULL THEN 1 END) as total_sent'),
                DB::raw('COUNT(CASE WHEN clicked_at IS NOT NULL THEN 1 END) as total_clicked')
            ])
            ->first();

        $sums['total'] += $slaveDb->total;
        $sums['total_sent'] += $slaveDb->total_sent;
        $sums['total_clicked'] += $slaveDb->total_clicked;

        return $sums;
    }

    public function getSentAndClicksByCampaign($campaign_id)
    {
        $mainDb = \DB::connection('mysql')
            ->table('broadcast_logs')
            ->where('campaign_id', $campaign_id)
            ->select([
                DB::raw('COUNT(CASE WHEN is_sent = true THEN 1 END) as total_sent'),
                DB::raw('COUNT(CASE WHEN is_click = true THEN 1 END) as total_clicked')
            ])
            ->first();

        $sums = (array)$mainDb;

        $slaveDb = \DB::connection('storage_mysql')
            ->table('broadcast_storage_master')
            ->where('campaign_id', $campaign_id)
            ->select([
                DB::raw('COUNT(CASE WHEN sent_at IS NOT NULL THEN 1 END) as total_sent'),
                DB::raw('COUNT(CASE WHEN clicked_at IS NOT NULL THEN 1 END) as total_clicked')
            ])
            ->first();

        $sums['total_sent'] += $slaveDb->total_sent;
        $sums['total_clicked'] += $slaveDb->total_clicked;

        return $sums;
    }

    public function getTotalSentAndClicksByBatch($batch_no)
    {
        $totals = \DB::connection('mysql')
            ->table('broadcast_logs')
            ->where('batch', $batch_no)
            ->select([
                DB::raw('COUNT(id) as total'),
                DB::raw('COUNT(CASE WHEN batch IS NOT NULL THEN 1 END) as total_processed'),
                DB::raw('COUNT(CASE WHEN is_sent = true THEN 1 END) as total_sent'),
                DB::raw('COUNT(CASE WHEN is_click = true THEN 1 END) as total_clicked')
            ])
            ->first();

        // TODO:: add archived campaigns for campaign mode

        return (array)$totals;
    }

    public function getTotalSentAndClicksByCampaignAndBatch($campaign_id, $batch_no)
    {
        $totals = \DB::connection('mysql')
            ->table('broadcast_logs')
            ->where('campaign_id', $campaign_id)
            ->where('batch', $batch_no)->select([
                DB::raw('COUNT(id) as total'),
                DB::raw('COUNT(CASE WHEN is_sent = true THEN 1 END) as total_sent'),
                DB::raw('COUNT(CASE WHEN is_click = true THEN 1 END) as total_clicked')
            ])
            ->first();

        // TODO:: add archived campaigns for campaign mode

        return (array)$totals;
    }

    public function getTotalCount()
    {
        return \DB::connection('mysql')
            ->table('broadcast_logs')
            ->count();
    }

    public function getArchivedTotalCount()
    {
        return \DB::connection('storage_mysql')
            ->table('broadcast_storage_master')
            ->count();
    }

    public function getClickedCount()
    {
        return \DB::connection('mysql')
            ->table('broadcast_logs')
            ->where('is_click', 1)
            ->whereNotNull('clicked_at')
            ->count();
    }

    public function getClickedCountByUserIds($userIds)
    {
        $userCampaignsIds = \DB::table('campaigns')->whereIn('user_id', $userIds)->pluck('id')->toArray();

        return $this->getClickedCountByCampaignIds($userCampaignsIds);
    }

    public function getClickedCountByCampaignIds(array $campaignIds)
    {
        return \DB::connection('mysql')
            ->table('broadcast_logs')
            ->where('is_click', 1)
            ->whereNotNull('clicked_at')
            ->whereIn('campaign_id', $campaignIds)
            ->count();
    }

    public function getArchivedClickedCount()
    {
        return \DB::connection('storage_mysql')
            ->table('broadcast_storage_master')
            ->whereNotNull('clicked_at')
            ->count();
    }

    public function getArchivedClickedCountByUserIds($userIds)
    {
        $userCampaignsIds = \DB::table('campaigns')->whereIn('user_id', $userIds)->pluck('id')->toArray();

        return $this->getArchivedClickedCountByCampaignIds($userCampaignsIds);
    }

    public function getArchivedClickedCountByCampaignIds(array $campaignIds)
    {
        return \DB::connection('storage_mysql')
            ->table('broadcast_storage_master')
            ->whereNotNull('clicked_at')
            ->whereIn('campaign_id', $campaignIds)
            ->count();
    }

    public function getSendCount()
    {
        return \DB::connection('mysql')
            ->table('broadcast_logs')
//            ->whereNotNull('sent_at')
//            ->where('is_sent', 1)
            ->whereNotNull('batch')
            ->count();
    }

    public function getSendCountByUserIds($userIds)
    {
        $userCampaignsIds = \DB::table('campaigns')->whereIn('user_id', $userIds)->pluck('id')->toArray();

        return $this->getSendCountByCampaignIds($userCampaignsIds);
    }

    public function getSendCountByCampaignIds(array $campaignIds)
    {
        return \DB::connection('mysql')
            ->table('broadcast_logs')
//            ->whereNotNull('sent_at')
            ->whereNotNull('batch')
            ->whereIn('campaign_id', $campaignIds)
            ->count();
    }

    public function getArchivedSendCount()
    {
        return \DB::connection('storage_mysql')
            ->table('broadcast_storage_master')
//            ->whereNotNull('sent_at')
            ->whereNotNull('batch')
            ->count();
    }

    public function getArchivedSendCountByUserIds($userIds)
    {
        $userCampaignsIds = \DB::table('campaigns')->whereIn('user_id', $userIds)->pluck('id')->toArray();

        return $this->getArchivedSendCountByCampaignIds($userCampaignsIds);
    }

    public function getArchivedSendCountByCampaignIds(array $campaignIds)
    {
        return \DB::connection('storage_mysql')
            ->table('broadcast_storage_master')
//            ->whereNotNull('sent_at')
            ->whereNotNull('batch')
            ->whereIn('campaign_id', $campaignIds)
            ->count();
    }
}
