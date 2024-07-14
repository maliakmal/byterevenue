<?php

namespace App\Repositories\Model\Campaign;

use App\Models\CampaignShortUrl;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Repositories\Model\BaseRepository;

class CampaignRepository extends BaseRepository implements CampaignRepositoryInterface
{
    public function __construct(CampaignShortUrl $model)
    {
        $this->model = $model;
    }
}
