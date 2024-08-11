<?php

namespace App\Services\OnePlusE\Requests\ESim;


use App\Services\OnePlusE\Requests\AbstractRequest;

class RemoveProfilesRequest extends AbstractRequest
{
    protected $path = '/esim-api/remove-profiles';
    protected $method = 'get';

    private $device_id;

    /**
     * @param $device_id
     */
    public function __construct($device_id)
    {
        $this->device_id = $device_id;
    }


    public function getRequestBody(array $extraInformation = null)
    {
        return [
            'device_id' => $this->device_id,
        ];
    }

    public function getRequestFiles(array $extraInformation = null)
    {
        return [];
    }
}
