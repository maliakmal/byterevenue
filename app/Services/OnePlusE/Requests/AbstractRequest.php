<?php

namespace App\Services\OnePlusE\Requests;

abstract class AbstractRequest
{
    protected $path;
    protected $method;

    private function getHostAddress()
    {
        return config('app.one_plus_e.host');
    }

    public function getPath()
    {
        return $this->getHostAddress().$this->path;
    }

    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string[]
     */
    public function getRequestHeader()
    {
        return [
            'Accept' => 'application/json',
        ];
    }
    abstract function getRequestBody(array $extraInformation = null);
    abstract function getRequestFiles(array $extraInformation = null);
}
