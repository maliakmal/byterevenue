<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\UrlShortener;
use App\Services\UrlShortener\UrlShortenerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShortDomainsApiController extends ApiController
{
    /**
     * @param UrlShortenerService $urlShortenerService
     */
    public function __construct(
        private UrlShortenerService $urlShortenerService,
    ) {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $shortDomains = $this->urlShortenerService->getAll($request);

        return $this->responseSuccess($shortDomains);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $response = $this->urlShortenerService->create($request);

        if (isset($response['error'])) {
            return $this->responseError(message: $response['error'], status:422);
        }

        return $this->responseSuccess($response, 'URL Shortener created successfully.');
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        if (UrlShortener::destroy($id)) {
            return $this->responseSuccess(message: 'URL Shortener deleted successfully.');
        }

        return $this->responseError(message: 'URL Shortener not found.', status: 404);
    }
}
