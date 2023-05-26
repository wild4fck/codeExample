<?php

declare(strict_types=1);

namespace App\Services\PackageChangeStatus\Exceptions;

use Throwable;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class ChangePackageStatusException extends BaseStatusException
{
    /**
     * @param string  $message
     * @param int  $code
     * @param \Throwable|null  $previous
     */
    public function __construct(
        string $message = 'Ошибка при смене статуса пакета',
        int $code = 400,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
