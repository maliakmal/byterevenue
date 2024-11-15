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
