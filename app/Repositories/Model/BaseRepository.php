<?php

namespace App\Repositories\Model;

use App\Repositories\Contract\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements BaseRepositoryInterface
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @return Collection
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function update(array $data, $id)
    {
        $user = $this->model->findOrFail($id);
        $user->update($data);
        return $user;
    }

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function updateByID(array $data, $id)
    {
        return $this->model->where('id', $id)->update($data);
    }

    /**
     * @param array $data
     * @param Model $model
     * @return Model
     */
    public function updateByModel(array $data, Model $model)
    {
        $model->fill($data);
        $model->save();
        return $model;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $user = $this->model->findOrFail($id);
        return$user->delete();
    }

    /**
     * @param Model $model
     * @return bool|null
     */
    public function deleteByModel(Model $model)
    {
        return $model->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * @param $field
     * @param $value
     * @return mixed
     */
    public function findBy($field, $value)
    {
        return $this->model->where($field, $value)->first();
    }

    /**
     * @param $perPage
     * @param $latest
     * @return LengthAwarePaginator
     */
    public function paginate($perPage, $latest = true)
    {
        $query = $this->model->newQuery();
        if($latest){
            $query = $query->latest();
        }
        return $query->paginate($perPage);
    }
}
