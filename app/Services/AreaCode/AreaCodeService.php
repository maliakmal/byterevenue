<?php

namespace App\Services\AreaCode;

use App\Models\AreaCode;
use Illuminate\Support\Facades\DB;

class AreaCodeService
{
    // Tags for cache available only redis driver
    // If you are using other driver, you can remove `->tags([self::CACHE_KEY])` from the methods

    const CACHE_KEY = 'area_data';
    const CACHE_TTL = 60 * 60 * 24; // 24 hour

    /**
     * @return array
     */
    public function getAreaData($caching = false): array
    {
        if ($caching) {
            return cache()->tags([self::CACHE_KEY])->remember(self::CACHE_KEY, self::CACHE_TTL, function () {
                return [
                    'provinces' => $this->getAllProvinces(),
                    'cities'    => $this->getAllCities()
                ];
            });
        }

        return [
            'provinces' => $this->getAllProvinces(),
            'cities'    => $this->getAllCities()
        ];
    }

    public function getAllProvinces($caching = false)
    {
        $query = AreaCode::select(DB::raw('distinct(province) AS province'))->orderBy('province');

        if ($caching) {
            return cache()->tags([self::CACHE_KEY])->remember(self::CACHE_KEY, self::CACHE_TTL, function () use ($query) {
                return $query->get();
            });
        }

        return $query->get();
    }

    public function getAllCities($caching = false)
    {
        $query = AreaCode::select(DB::raw('distinct(city_name) AS city_name, province, code'))
            ->orderBy('city_name');

        if ($caching) {
            return cache()->tags([self::CACHE_KEY])->remember(self::CACHE_KEY, self::CACHE_TTL, function () use ($query) {
                return $query->get();
            });
        }

        return $query->get();
    }

    public function getCities(string $province, $caching = false)
    {
        $query = AreaCode::select(DB::raw('distinct(city_name) AS city_name, province, code'))
            ->where('province', $province)
            ->orderBy('city_name');

        if ($caching) {
            return cache()->tags([self::CACHE_KEY])->remember(self::CACHE_KEY . $province, self::CACHE_TTL, function () use ($query) {
                return $query->get();
            });
        }

        return $query->get();
    }
}
