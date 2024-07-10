<?php

namespace App\Services\Keitaro;

use App\Services\Keitaro\Requests\AbstractRequest;
use Illuminate\Support\Facades\Http;

class KeitaroCaller
{
    public function call(AbstractRequest $request)
    {
        $method = $request->getMethod();
        $path = $request->getPath();
        $headers = $request->getRequestHeader();
        $body = $request->getRequestBody();
        $response = Http::retry(3)->withHeaders($headers)
            ->$method($path, $body);
        $response->throw();
        return $response->json();
    }
}
