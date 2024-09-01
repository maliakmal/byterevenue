<?php

namespace App\Repositories\Model\BatchFile;

use App\Models\BatchFile;
use App\Repositories\Contract\BatchFile\BatchFileRepositoryInterface;
use App\Repositories\Model\BaseRepository;

class BatchFileRepository extends BaseRepository implements BatchFileRepositoryInterface
{
    public function __construct(BatchFile $model)
    {
        $this->model = $model;
    }
    public function getByCampaignId($campaign_id){
    }

}
