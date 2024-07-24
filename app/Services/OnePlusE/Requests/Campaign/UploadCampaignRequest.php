<?php

namespace App\Services\OnePlusE\Requests\Campaign;


use App\Services\OnePlusE\Requests\AbstractRequest;

class UploadCampaignRequest extends AbstractRequest
{
    protected $path = '/camp-prep-api/upload-campaign';
    protected $method = 'post';

    private $campaign_file;

    /**
     * @param $campaign_file
     */
    public function __construct($campaign_file)
    {
        $this->campaign_file = $campaign_file;
    }


    public function getRequestBody(array $extraInformation = null)
    {
        return [];
    }

    public function getRequestFiles(array $extraInformation = null)
    {
        return [
            'campaign_file' => $this->campaign_file
        ];
    }
}
