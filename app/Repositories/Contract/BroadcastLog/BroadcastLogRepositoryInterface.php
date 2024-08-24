<?php

namespace App\Repositories\Contract\BroadcastLog;

use App\Repositories\Contract\BaseRepositoryInterface;

interface BroadcastLogRepositoryInterface extends BaseRepositoryInterface
{
    public function updateWithIDs(array $ids, $fieldsToUpdate);

    public function paginateBroadcastLogs(array $inputs, bool $paginate);

    public function requeueUnsent(array $inputs);
    public function getUnsent(array $inputs);

    public function getUniqueCampaignsIDs($limit);
    public function getUniqueCampaignsIDsFromExistingBatch($batch);
}
