<?php

namespace App\Services\PackageAvailableStatus\Exceptions;

use Exception;
use Throwable;

class ForbiddenPackageStatusException extends Exception
{
    public function __construct(
        string $message = 'Действие запрещено на данном статусе',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
