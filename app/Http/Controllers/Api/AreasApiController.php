<?php

namespace App\Http\Controllers\Api;

use App\Services\AreaCode\AreaCodeService;

class AreasApiController extends ApiController
{
    public function __construct(private AreaCodeService $areaCodeService)
    {
        //
    }

    public function getAllProvinces()
    {
        return response()->json($this->areaCodeService->getAllProvinces());
    }

    public function getAllCities()
    {
        return response()->json($this->areaCodeService->getAllCities());
    }

    public function citiesByProvince(string $province)
    {
        return response()->json($this->areaCodeService->getCities($province));
    }
}
