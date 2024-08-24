<?php

namespace App\Repositories\Model\BlackListWord;

use App\Models\BlackListWord;
use App\Repositories\Contract\BlackListWord\BlackListWordRepositoryInterface;
use App\Repositories\Model\BaseRepository;

class BlackListWordRepository extends BaseRepository implements BlackListWordRepositoryInterface
{
    public function __construct(BlackListWord $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $upsertFields
     * @return mixed
     */
    public function upsertWord(array $upsertFields)
    {
        return $this->model->upsert($upsertFields, ['word'], ['updated_at']);
    }
}
