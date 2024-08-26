<?php

namespace App\Repositories\Contract\Campaign;

use App\Repositories\Contract\BaseRepositoryInterface;

interface CampaignRepositoryInterface extends BaseRepositoryInterface
{
    public function getCampaignsForUser(int $userID);

    public function getPendingCampaigns(array $params);

    public function markCampaignAsDone($campaign_id);

    public function reportCampaigns(array $inputs, array $selectColumns = [], bool $paginate = true);
}
