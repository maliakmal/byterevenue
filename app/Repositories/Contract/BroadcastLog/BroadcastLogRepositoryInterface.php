<?php

namespace App\Repositories\Contract\BroadcastLog;

use App\Repositories\Contract\BaseRepositoryInterface;

interface BroadcastLogRepositoryInterface extends BaseRepositoryInterface
{
    public function updateWithIDs(array $ids, $fieldsToUpdate);

    public function paginateBroadcastLogs(array $inputs);
}
