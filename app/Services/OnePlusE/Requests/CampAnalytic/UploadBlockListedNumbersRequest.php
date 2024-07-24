<?php

namespace App\Services\OnePlusE\Requests\CampAnalytic;


use App\Services\OnePlusE\Requests\AbstractRequest;

class UploadBlockListedNumbersRequest extends AbstractRequest
{
    protected $path = '/analytics-api/upload-blacklisted-numbers';
    protected $method = 'post';

    private $file;

    /**
     * @param $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }


    public function getRequestBody(array $extraInformation = null)
    {
        return [];
    }

    public function getRequestFiles(array $extraInformation = null)
    {
        return [
            'file' => $this->file,
        ];
    }
}
