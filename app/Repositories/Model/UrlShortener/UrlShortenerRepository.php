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

}
