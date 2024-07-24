<?php

namespace App\Services\OnePlusE\Requests\SendMMS;


use App\Services\OnePlusE\Requests\AbstractRequest;

class StopSendingRequest extends AbstractRequest
{
    protected $path = '/send-api/stop-sending';
    protected $method = 'get';

    public function __construct()
    {

    }


    public function getRequestBody(array $extraInformation = null)
    {
        return [];
    }

    public function getRequestFiles(array $extraInformation = null)
    {
        return [];
    }
}
