<?php

namespace App\Services\OnePlusE\Requests\SendMMS;


use App\Services\OnePlusE\Requests\AbstractRequest;

class GetAcceptsCountRequest extends AbstractRequest
{
    protected $path = '/send-api/get-accepts-count';
    protected $method = 'get';

    public function getRequestBody(array $extraInformation = null)
    {
        return [];
    }

    public function getRequestFiles(array $extraInformation = null)
    {
        return [];
    }
}
