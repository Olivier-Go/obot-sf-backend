<?php

namespace App\Utils;

use DateInterval;
use DateTime;

abstract class Tools
{
    /**
     * Convert timestamp in seconds to date format
     * @param string|null $timestamp
     * @return string
     */
    protected function convertTimestampSec(?string $timestamp): ?string
    {
        $dateString = null;
        if (!empty($timestamp)) {
            $date = DateTime::createFromFormat('U', $timestamp);
            $date->add(new DateInterval('PT2H'));
            $dateString = $date->format('m/d/Y H:i:s');
        }
        return $dateString;
    }

    /**
     * Convert timestamp in ms to date format
     * @param string|null $msTimestamp
     * @return string
     */
    protected function convertTimestampMs(?string $msTimestamp): ?string
    {
        $dateString = null;
        $timestampSec = number_format($msTimestamp / 1000, 0, ',', '');
        if (!empty($timestampSec)) {
            $date = DateTime::createFromFormat('U', $timestampSec);
            $date->add(new DateInterval('PT2H'));
            $dateString = $date->format('m/d/Y H:i:s');
        }
        return $dateString;
    }

    /**
     * Search in multidimensional array for a given value and returns the first corresponding key if successful
     * @param array $array
     * @param string $field
     * @param string $value
     * @return false|int|string
     */
    public static function array_search_nested(array $array, string $field, string $value)
    {
        foreach($array as $key => $element) {
            if (isset($element[$field]) && $element[$field] === $value) return $key;
        }
        return false;
    }
}