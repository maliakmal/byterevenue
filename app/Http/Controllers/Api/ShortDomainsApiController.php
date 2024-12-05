<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\UrlShortener;
use App\Services\Keitaro\KeitaroCaller;
use App\Services\Keitaro\Requests\Domains\RegisterShortDomainRequest;
use App\Services\UrlShortener\UrlShortenerService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;

class ShortDomainsApiController extends ApiController
{
    /**
     * @param UrlShortenerService $urlShortenerService
     */
    public function __construct(
        private UrlShortenerService $urlShortenerService,
    ) {}

    public function index(Request $request)
    {
        $shortDomains = $this->urlShortenerService->getAll($request);
        return $this->responseSuccess($shortDomains);
    }

    public function store(Request $request)
    {
        $response = $this->urlShortenerService->create($request);

        if (isset($response['error'])) {
            return $this->responseError(data: $response['error'],status:422);
        }

        return $this->responseSuccess($response, 'URL Shortener created successfully.');
    }

    public function destroy(int $id)
    {
        UrlShortener::destroy($id);
        return $this->responseSuccess(message: 'URL Shortener deleted successfully.');
    }
}
