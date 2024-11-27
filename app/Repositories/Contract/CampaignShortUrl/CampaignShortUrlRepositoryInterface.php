<?php

namespace App\Repositories\Contract\CampaignShortUrl;

use App\Repositories\Contract\BaseRepositoryInterface;

interface CampaignShortUrlRepositoryInterface extends BaseRepositoryInterface
{
    public function findWithCampaignIDUrlID($campaignID, $url);
    public function search(array $params);
    public function getIncomplete();

}
