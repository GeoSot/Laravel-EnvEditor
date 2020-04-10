<?php

namespace GeoSot\EnvEditor\Exceptions;

use Exception;

class EnvException extends Exception
{
    public function __construct($message, $code, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__.":[{$this->code}]: {$this->message}\n";
    }
}
