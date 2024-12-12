<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\ApiController;
use App\Services\AreaCode\AreaCodeService;
use Illuminate\Http\JsonResponse;

class AreasController extends ApiController
{
    /**
     * @param AreaCodeService $areaCodeService
     */
    public function __construct(
        private AreaCodeService $areaCodeService,
    ) {}

    /**
     * @return JsonResponse
     */
    public function getAllProvinces(): JsonResponse
    {
        return $this->responseSuccess($this->areaCodeService->getAllProvinces(caching: true));
    }

    /**
     * @return JsonResponse
     */
    public function getAllCities(): JsonResponse
    {
        return $this->responseSuccess($this->areaCodeService->getAllCities(caching: true));
    }

    /**
     * @param string $province
     * @return JsonResponse
     */
    public function citiesByProvince(string $province): JsonResponse
    {
        return $this->responseSuccess($this->areaCodeService->getCities($province, caching: true));
    }
}
