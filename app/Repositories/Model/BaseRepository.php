<?php

namespace App\Repositories\Model;

use App\Repositories\Contract\BaseRepositoryInterface;
use BaconQrCode\Common\Mode;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements BaseRepositoryInterface
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(array $data, $id)
    {
        $user = $this->model->findOrFail($id);
        $user->update($data);
        return $user;
    }
    public function updateByModel(array $data, Model $model)
    {
        $model->fill($data);
        $model->save();
        return $model;
    }

    public function delete($id)
    {
        $user = $this->model->findOrFail($id);
        return$user->delete();
    }
    public function deleteByModel(Model $model)
    {
        return $model->delete();
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }
}
