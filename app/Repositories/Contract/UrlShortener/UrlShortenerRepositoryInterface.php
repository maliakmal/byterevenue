<?php

namespace App\Repositories\Contract\UrlShortener;

use App\Repositories\Contract\BaseRepositoryInterface;

interface UrlShortenerRepositoryInterface extends BaseRepositoryInterface
{
    public function search(array $params);

}
