<?php

namespace App\Services\OnePlusE\Requests\ESim;


use App\Services\OnePlusE\Requests\AbstractRequest;

class RunESIMScriptRequest extends AbstractRequest
{
    protected $path = '/esim-api/run-esim-script';
    protected $method = 'post';

    private $esim_code;

    /**
     * @param $esim_code
     */
    public function __construct($esim_code)
    {
        $this->esim_code = $esim_code;
    }

    public function getRequestBody(array $extraInformation = null)
    {
        return [
            'esim_code' => $this->esim_code
        ];
    }

    public function getRequestFiles(array $extraInformation = null)
    {
        return [];
    }
}
