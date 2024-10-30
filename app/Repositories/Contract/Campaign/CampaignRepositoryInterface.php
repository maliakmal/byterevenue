<?php

namespace App\Repositories\Contract\Campaign;

use App\Repositories\Contract\BaseRepositoryInterface;

interface CampaignRepositoryInterface extends BaseRepositoryInterface
{
    public function getCampaignsForUser(int $userID);

    public function getPendingCampaigns(array $params);

    public function markCampaignAsDone($campaign_id);

    public function getUnsentByIds(array $ids);

    public function getUnsentByIdsOfUser(array $ids, $user_id = null);

    public function reportCampaigns(array $inputs, array $selectColumns = []);
}
