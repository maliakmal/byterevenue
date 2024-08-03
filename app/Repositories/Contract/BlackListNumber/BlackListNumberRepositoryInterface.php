<?php

namespace App\Repositories\Contract\BlackListNumber;

use App\Repositories\Contract\BaseRepositoryInterface;

interface BlackListNumberRepositoryInterface extends BaseRepositoryInterface
{
    public function upsertPhoneNumber(array $upsertFields);
}
