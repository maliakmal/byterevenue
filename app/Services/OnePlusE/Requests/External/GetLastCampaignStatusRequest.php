<?php

namespace App\Services\OnePlusE\Requests\External;


use App\Services\OnePlusE\Requests\AbstractRequest;

class GetLastCampaignStatusRequest extends AbstractRequest
{
    protected $path = '/post-camp-api/get-uid-stats';
    protected $method = 'get';

    public function getRequestFiles(array $extraInformation = null)
    {
        return [];
    }

    public function getRequestBody(array $extraInformation = null)
    {
        return [];
    }
}
