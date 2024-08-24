<?php

namespace App\Repositories\Contract\BlackListWord;

use App\Repositories\Contract\BaseRepositoryInterface;

interface BlackListWordRepositoryInterface extends BaseRepositoryInterface
{
    public function upsertWord(array $upsertFields);
}
