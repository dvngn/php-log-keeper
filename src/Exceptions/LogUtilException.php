<?php

namespace Devengine\LogKeeper\Exceptions;

use Exception;

class LogUtilException extends Exception
{
    public static function invalidFileDateFormat(string $filename, string $format): static
    {
        return new static("The provided filename `$filename` doesn't have a valid date format `$format`.");
    }
}