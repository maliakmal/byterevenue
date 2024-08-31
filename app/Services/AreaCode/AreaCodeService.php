<?php

namespace App\Services\AreaCode;

use App\Models\AreaCode;
use Illuminate\Support\Facades\DB;
class AreaCodeService
{


    /**
     * @return array
     */
    public function getAreaData() : array
    {
        $result = [];
        $result['provinces'] = AreaCode::select(DB::raw('distinct(province) AS province'))->orderBy('province')->get();
        $result['cities'] = AreaCode::select(DB::raw('distinct(city_name) AS city_name, province, code'))->orderBy('city_name')->get();
        return $result;
    }
}
