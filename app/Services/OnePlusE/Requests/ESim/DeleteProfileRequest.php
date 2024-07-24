<?php

namespace App\Services\OnePlusE\Requests\ESim;


use App\Services\OnePlusE\Requests\AbstractRequest;

class DeleteProfileRequest extends AbstractRequest
{
    protected $path = '/esim-api/delete-profile';
    protected $method = 'get';

    private $ICCID;

    /**
     * @param $ICCID
     */
    public function __construct($ICCID)
    {
        $this->ICCID = $ICCID;
    }


    public function getRequestBody(array $extraInformation = null)
    {
        return [
            'ICCID' => $this->ICCID,
        ];
    }

    public function getRequestFiles(array $extraInformation = null)
    {
        return [];
    }
}
