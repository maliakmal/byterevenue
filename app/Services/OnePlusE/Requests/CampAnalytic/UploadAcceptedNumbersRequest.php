<?php

namespace App\Services\OnePlusE\Requests\CampAnalytic;


use App\Services\OnePlusE\Requests\AbstractRequest;

class UploadAcceptedNumbersRequest extends AbstractRequest
{
    protected $path = '/analytics-api/upload-accepted-numbers';
    protected $method = 'post';

    private $file;
    private $camp_name;

    /**
     * @param $file
     * @param $camp_name
     */
    public function __construct($file, $camp_name)
    {
        $this->file = $file;
        $this->camp_name = $camp_name;
    }


    public function getRequestBody(array $extraInformation = null)
    {
        return [
            'camp_name' => $this->camp_name,
        ];
    }

    public function getRequestFiles(array $extraInformation = null)
    {
        return [
            'file' => $this->file,
        ];
    }
}
