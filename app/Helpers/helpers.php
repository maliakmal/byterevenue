<?php

use Hidehalo\Nanoid\Client;
use App\Dictionary\Constants;

/**
 * create extendedNanoId nanoId slug with custom alphabet
 *
 * @param int $length
 * @return string
 */
if (! function_exists('extendedNanoId')) {
    // has some problem with result format
    function extendedNanoId(int $length = 8, string $alphabet = Constants::EXTENDED_ALPHABET)
    {
        $client = new Client();

        return $client->formattedId($alphabet, $length);
    }
}

/**
 * custom dump and die in json format
 *
 * @param mixed $data
 */
if (! function_exists('_dd')) {
    function _dd($data)
    {
        throw new \Exception(json_encode($data, JSON_PRETTY_PRINT));
    }
}
