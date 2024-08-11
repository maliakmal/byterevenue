<?php

namespace App\Services\OnePlusE\Requests\QMI;


use App\Services\OnePlusE\Requests\AbstractRequest;

class SetDeviceIDRequest extends AbstractRequest
{
    protected $path = '/qmi-api/set-device-id';
    protected $method = 'post';

    private $device_id;

    /**
     * @param $device_id
     */
    public function __construct($device_id,)
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
