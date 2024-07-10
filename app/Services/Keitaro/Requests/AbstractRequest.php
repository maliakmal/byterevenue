<?php

namespace App\Services\Keitaro\Requests;

abstract class AbstractRequest
{
    protected $path;
    protected $method;

    private function getHostAddress()
    {
        return config('app.keitaro.host');
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
            'Content-Type' => 'Application/json',
            'Api-Key' => config('app.keitaro.token')
        ];
    }
    abstract function getRequestBody(array $extraInformation = null);
}
