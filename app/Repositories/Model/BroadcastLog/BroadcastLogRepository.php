<?php

namespace App\Repositories\Model\BroadcastLog;

use App\Models\BroadcastLog;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Model\BaseRepository;

class BroadcastLogRepository extends BaseRepository implements BroadcastLogRepositoryInterface
{
    public function __construct(BroadcastLog $model)
    {
        $this->model = $model;
    }
}
