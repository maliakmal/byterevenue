<?php

namespace App\Services\OnePlusE\Requests\SendMMS;


use App\Services\OnePlusE\Requests\AbstractRequest;

class SendCampaignRequest extends AbstractRequest
{
    protected $path = '/send-api/send-campaign';
    protected $method = 'get';

    private $headers;
    private $bcc_count;
    public function __construct($headers = null, $bcc_count = null)
    {
        $this->headers = $headers;
        $this->bcc_count = $bcc_count;
    }


    public function getRequestBody(array $extraInformation = null)
    {
        return [
            'headers' => $this->headers,
            'bcc_count' => $this->bcc_count,
        ];
    }

    public function getRequestFiles(array $extraInformation = null)
    {
        return [];
    }
}
