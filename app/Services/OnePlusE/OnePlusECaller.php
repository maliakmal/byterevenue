<?php

namespace App\Services\OnePlusE;

use App\Services\OnePlusE\Requests\AbstractRequest;
use Illuminate\Support\Facades\Http;

class OnePlusECaller
{
    public function call(AbstractRequest $request)
    {
        $method = $request->getMethod();
        $path = $request->getPath();
        $headers = $request->getRequestHeader();
        $body = $request->getRequestBody();
        $attachments = $request->getRequestFiles();
        $http = Http::retry(3);
        if(is_array($attachments) && count($attachments) > 0){
            foreach ($attachments as $key => $value){
                $http = $http->attach($key, $value);
            }
        }
        $response = $http->withHeaders($headers)
            ->$method($path, $body);
        $response->throw();
        return $response->json();
    }
}
