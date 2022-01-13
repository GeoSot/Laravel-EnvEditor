<?php

namespace GeoSot\EnvEditor\Exceptions;

use Exception;

class EnvException extends Exception
{
    public function __toString()
    {
        return __CLASS__.":[{$this->code}]: {$this->message}\n";
    }
}
