<?php

namespace App\Dictionary;

/**
* Interface Constants
*/
interface Constants
{
    /** @varstringUUID_REGEX */
    const UUID_REGEX = '([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})';

    /** @varstringULID_REGEX */
    const ULID_REGEX = '^[0-9A-Z]{26}$';

    /** @varstringPHONE_FORMAT */
    const PHONE_REGEX = '^\+?[0-9]{15}$';

    // Alphabet with legal utf8mb4 characters
    /** @varstringEXTENDED_ALPHABET */
    const EXTENDED_ALPHABET = '0123456789'.
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
        'abcdefghijklmnopqrstuvwxyz'.
        'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝÞß'.
        'àáâãäåæçèéêëìíîïñòóôõöøùúûüýþÿ'.
        'ΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩ'.
        'αβγδεζηθικλμνξοπρστυφχψω'.
        'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ'.
        'абвгдеёжзийклмнопрстуфхцчшщъыьэюя';
}
