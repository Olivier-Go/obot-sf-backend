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
            $date->add(new DateInterval('PT1H'));
            $dateString = $date->format('m/d/Y H:i:s');
        }
        return $dateString;
    }

    /**
     * Convert timestamp in ms to date format
     * @param String $msTimestamp
     * @return string
     */
    protected function convertTimestampMs(?string $msTimestamp): ?string
    {
        $dateString = null;
        $timestampSec = number_format($msTimestamp / 1000, 0, ',', '');
        if (!empty($timestampSec)) {
            $date = DateTime::createFromFormat('U', $timestampSec);
            $date->add(new DateInterval('PT1H'));
            $dateString = $date->format('m/d/Y H:i:s');
        }
        return $dateString;
    }
}