<?php

namespace App\Services\BlacklistNumber;

use App\Models\BlackListNumber;
use App\Services\Unitily\UtilityService;

class BlacklistNumberService
{
    /**
     * @param UtilityService $utilityService
     */
    public function __construct(private UtilityService $utilityService) {}

    /**
     * @param array $data
     *
     * @return BlackListNumber
     */
    public function store(array $data)
    {
        $phoneNumberString = $this->utilityService->formatPhoneNumber($data['phone_number']);
        $list = collect(explode("\n", $phoneNumberString));
        $list = $list->reject(function ($item){
            return empty($item);
        });

        $list = $list->map(function ($item) {
            return ['phone_number' => trim($item)];
        });

       return BlackListNumber::upsert(
            $list->toArray(), ['phone_number'],['updated_at' => now()]
        );
    }
}
