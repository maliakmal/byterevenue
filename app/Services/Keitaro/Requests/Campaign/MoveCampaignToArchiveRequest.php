<?php

namespace App\Services\Keitaro\Requests\Campaign;

use App\Services\Keitaro\Requests\AbstractRequest;

class MoveCampaignToArchiveRequest extends AbstractRequest
{
    protected $path = '/admin_api/v1/campaigns/id';
    protected $method = 'delete';

    private $id;

    public function __construct($id)
    {
        $this->id = $id;
        $this->path = str_replace('id', $this->id, $this->path);
    }


    public function getRequestBody(array $extraInformation = null)
    {
        return [

        ];
    }
}
