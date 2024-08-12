<?php

namespace App\Services\OnePlusE\Requests\SendMMS;


use App\Services\OnePlusE\Requests\AbstractRequest;

class SendSingleRequest extends AbstractRequest
{
    protected $path = '/send-api/send-single';
    protected $method = 'get';

    private $number;
    private $text;
    public function __construct($number = null, $text = null)
    {
        $this->number = $number;
        $this->text = $text;
    }


    public function getRequestBody(array $extraInformation = null)
    {
        return [
            'number' => $this->number,
            'text' => $this->text,
        ];
    }

    public function getRequestFiles(array $extraInformation = null)
    {
        return [];
    }
}
