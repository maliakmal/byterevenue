<?php

namespace App\Repositories\Model\User;

use App\Models\User;
use App\Repositories\Contract\User\UserRepositoryInterface;
use App\Repositories\Model\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        $this->model = $model;
    }

}
