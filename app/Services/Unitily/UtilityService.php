<?php

namespace App\Services\Unitily;

class UtilityService
{
    /**
     * @param string $phoneNumber
     * @return string
     */
    public function formatPhoneNumber(string $phoneNumber) : string
    {
        $phone_number = str_replace('+', '', $phoneNumber);
        $phone_number = str_replace('(', '', $phoneNumber);
        $phone_number = str_replace(')', '', $phoneNumber);
        $phone_number = str_replace('-', '', $phoneNumber);
        $phone_number = str_replace(' ', '', $phoneNumber);
        return trim($phoneNumber);
    }

}
