<?php

namespace App\Repositories\Model\Setting;

use App\Models\Setting;
use App\Repositories\Contract\Setting\SettingRepositoryInterface;
use App\Repositories\Model\BaseRepository;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    public function __construct(Setting $model)
    {
        $this->model = $model;
    }
}
