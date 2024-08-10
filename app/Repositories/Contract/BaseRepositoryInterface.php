<?php

namespace App\Repositories\Contract;

use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function all();

    public function create(array $data);

    public function update(array $data, $id);

    public function updateByID(array $data, $id);

    public function updateByModel(array $data, Model $model);

    public function delete($id);

    public function deleteByModel(Model $model);

    public function find($id);

    public function paginate($perPage, $latest = true);
}
