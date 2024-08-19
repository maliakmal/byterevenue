<?php

namespace App\Repositories\Model\UrlShortener;

use App\Models\UrlShortener;
use App\Repositories\Contract\UrlShortener\UrlShortenerRepositoryInterface;
use App\Repositories\Model\BaseRepository;

class UrlShortenerRepository extends BaseRepository implements UrlShortenerRepositoryInterface
{
    public function __construct(UrlShortener $model)
    {
        $this->model = $model;
    }
    /**
     * @param array $search
     * @return mixed
     */
    public function search(array $params)
    {
        $q = $this->model->query();
        if(isset($params['name']) && !empty($params['name'])){
            $q = $q->where('name',  trim($params['name']));
        }

        return   $q->get()->first();
    }

}
