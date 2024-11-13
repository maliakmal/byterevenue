<?php

namespace App\Repositories\Contract\BroadcastLog;

use App\Repositories\Contract\BaseRepositoryInterface;

interface BroadcastLogRepositoryInterface extends BaseRepositoryInterface
{
    public function updateWithIDs(array $ids, $fieldsToUpdate);

    public function paginateBroadcastLogs(array $inputs);

    public function requeueUnsent(array $inputs);

    public function getUnsent(array $inputs);

    public function getQueueStats();

    public function getUniqueCampaignsIDs($limit = null);

    public function getUniqueCampaignsIDsFromExistingBatch($batch);

    public function getTotalSentAndClicksByCampaign($campaign_id);

    public function getTotalSentAndClicksByBatch($batch);

    public function getTotalSentAndClicksByCampaignAndBatch($campaign_id, $batch_no);
}
