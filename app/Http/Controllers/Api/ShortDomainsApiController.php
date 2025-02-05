<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Middleware\CheckAdminRole;
use App\Models\CampaignShortUrl;
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
        $this->middleware(['auth:sanctum', CheckAdminRole::class]);
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
        $request->validate([
            'endpoints' => 'required|array',
            'endpoints.*' => 'required|string|max:180|unique:url_shorteners,name',
        ]);

        // create new short domain records
        $response = $this->urlShortenerService->create($request->endpoints);

        if (isset($response['error'])) {
            return $this->responseError(message: $response['error'], status:422);
        }

        return $this->responseSuccess(message: $response['message']);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $biddingRecords = CampaignShortUrl::where('url_shortener_id', $id)->first();

        if ($biddingRecords) {
            return $this->responseError(message: 'URL Shortener cannot be deleted because it is being used in a campaign.', status: 422);
        }

        if (UrlShortener::destroy($id)) {
            return $this->responseSuccess(message: 'URL Shortener deleted successfully.');
        }

        return $this->responseError(message: 'URL Shortener not found.', status: 404);
    }
}
