<?php

namespace App\Services\Keitaro\Requests\Campaign;

use App\Services\Keitaro\Requests\AbstractRequest;

class GetAllCampaignsRequest extends AbstractRequest
{
    protected $path = '/admin_api/v1/campaigns';
    protected $method = 'get';

    private $limit;
    private $offset;

    /**
     * @param $limit
     * @param $offset
     */
    public function __construct($limit, $offset)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }


    public function getRequestBody(array $extraInformation = null)
    {
        return [
            'limit' => $this->limit,
            'offset' => $this->offset,
        ];
    }
}
