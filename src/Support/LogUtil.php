<?php

namespace Devengine\LogKeeper\Support;

use Carbon\Carbon;
use DateTimeInterface;
use Devengine\LogKeeper\Exceptions\LogUtilException;

class LogUtil
{
    /**
     * @throws LogUtilException
     */
    public static function parseDate(string $filename): DateTimeInterface
    {
        if (preg_match('/(?<date>\d{4}-\d{2}-\d{2})\.*/', $filename, $matches)) {
            return Carbon::createFromFormat('Y-m-d', $matches['date']);
        }

        throw LogUtilException::invalidFileDateFormat($filename, 'Y-m-d');
    }
}