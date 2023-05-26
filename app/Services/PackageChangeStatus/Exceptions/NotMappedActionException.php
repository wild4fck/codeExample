<?php

declare(strict_types=1);

namespace App\Services\PackageChangeStatus\Exceptions;

use Throwable;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class NotMappedActionException extends BaseStatusException
{
    protected $message = 'Передан неизвестный тип пакета для обработчика изменения статуса';
    protected $code = 401;
    
    /**
     * @param string  $packageType
     * @param \Throwable|null  $previous
     */
    public function __construct(string $packageType, Throwable $previous = null)
    {
        $message = $this->message . ": " . $packageType;
        parent::__construct($message, $this->code, $previous);
    }
}