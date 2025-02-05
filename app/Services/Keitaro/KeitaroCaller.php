<?php

namespace App\Services\Keitaro;

use App\Models\RequestLog;
use App\Services\Keitaro\Requests\AbstractRequest;
use Illuminate\Support\Facades\Http;

class KeitaroCaller
{
    static public function call(AbstractRequest $request)
    {
        $method = $request->getMethod();
        $path = $request->getPath();
        $headers = $request->getRequestHeader();
        $body = $request->getRequestBody();

        $log = new RequestLog();

        $log->request = json_encode([
            'method' => $method,
            'path' => $path,
            'headers' => $headers,
            'body' => $body,
        ]);

        try {
            $response = Http::retry(3)->withHeaders($headers)
                ->$method($path, $body);
            $response->throw();

            $log->response = json_encode($response->json());
            $log->status_code = $response->status();

        } catch (\Exception $e) {
            $log->exception = $e->getMessage();
        }

        $log->save();

        return isset($response) ? $response->json() : ['error' => $e->getMessage()];
    }
}
