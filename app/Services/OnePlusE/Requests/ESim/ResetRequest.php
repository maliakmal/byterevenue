<?php

namespace App\Services\OnePlusE\Requests\ESim;


use App\Services\OnePlusE\Requests\AbstractRequest;

class ResetRequest extends AbstractRequest
{
    protected $path = '/esim-api/reset';
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
