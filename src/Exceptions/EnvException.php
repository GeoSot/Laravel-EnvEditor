<?php

namespace GeoSot\EnvEditor\Exceptions;

class EnvException extends \Exception
{
    public function __toString()
    {
        return __CLASS__.":[{$this->code}]: {$this->message}\n";
    }
}
