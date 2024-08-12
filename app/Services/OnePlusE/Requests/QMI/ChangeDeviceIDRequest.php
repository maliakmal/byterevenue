<?php

namespace App\Services\OnePlusE\Requests\QMI;


use App\Services\OnePlusE\Requests\AbstractRequest;

class ChangeDeviceIDRequest extends AbstractRequest
{
    protected $path = '/qmi-api/change-device-id';
    protected $method = 'post';

    private $device_id;
    private $ip_type;
    private $apn;

    /**
     * @param $device_id
     * @param $ip_type
     * @param $apn
     */
    public function __construct($ip_type, $apn, $device_id = null)
    {
        $this->device_id = $device_id;
        $this->ip_type = $ip_type;
        $this->apn = $apn;
    }


    public function getRequestBody(array $extraInformation = null)
    {
        return [
            'apn'       => $this->apn,
            'ip_type'   => $this->ip_type,
            'device_id' => $this->device_id,
        ];
    }

    public function getRequestFiles(array $extraInformation = null)
    {
        return [];
    }
}
