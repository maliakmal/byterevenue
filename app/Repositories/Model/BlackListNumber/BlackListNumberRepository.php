<?php

namespace App\Repositories\Model\BlackListNumber;

use App\Models\BlackListNumber;
use App\Repositories\Contract\BlackListNumber\BlackListNumberRepositoryInterface;
use App\Repositories\Model\BaseRepository;

class BlackListNumberRepository extends BaseRepository implements BlackListNumberRepositoryInterface
{
    public function __construct(BlackListNumber $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $upsertFields
     * @return mixed
     */
    public function upsertPhoneNumber(array $upsertFields)
    {
        return $this->model->upsert($upsertFields, ['phone_number'], ['phone_number']);
    }
}
