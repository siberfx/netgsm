<?php

namespace Siberfx\NetGsm\Exceptions;

use Exception;
use Throwable;

abstract class AbstractNetGsmException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct((string) trans($message), $code, $previous);
    }
}
