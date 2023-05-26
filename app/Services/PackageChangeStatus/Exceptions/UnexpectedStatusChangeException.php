<?php

declare(strict_types=1);

namespace App\Services\PackageChangeStatus\Exceptions;

use Throwable;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class UnexpectedStatusChangeException extends BaseStatusException
{
    /**
     * @param string  $message
     * @param int  $code
     * @param \Throwable|null  $previous
     */
    public function __construct(
        string $message = 'Переход пакета на запрошенный статус из текущего не предусмотрен.',
        int $code = 400,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
}
