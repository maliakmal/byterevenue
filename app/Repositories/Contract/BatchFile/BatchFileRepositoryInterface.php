<?php

namespace App\Repositories\Contract\BatchFile;

use App\Repositories\Contract\BaseRepositoryInterface;

interface BatchFileRepositoryInterface extends BaseRepositoryInterface
{
    public function getByCampaignId($campaign_id);

}
