<?php

declare(strict_types=1);

namespace App\Services\PackageChangeStatus\Exceptions;

use Throwable;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class ChangeSameStatusException extends BaseStatusException
{
    /**
     * @param string  $message
     * @param int  $code
     * @param \Throwable|null  $previous
     */
    public function __construct(string $message = 'Документ пытается сменить статус на свой же', int $code = 402, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
