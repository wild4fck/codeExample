<?php

declare(strict_types=1);

namespace App\Services\PackageChangeStatus\Exceptions;

use Throwable;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class PackageStatusValidateException extends BaseStatusException
{
    /**
     * @var null|array
     */
    protected ?array $necessaryActions;
    
    /**
     * @param string  $message
     * @param null|array  $necessaryActions
     * @param int  $code
     * @param \Throwable|null  $previous
     */
    public function __construct(
        string $message = 'Ошибка валидации доступности статуса',
        array $necessaryActions = null,
        int $code = 403,
        Throwable $previous = null
    ) {
        $this->necessaryActions = $necessaryActions;
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Какие действия необходимо выполнить
     *
     * @return null|array
     */
    public function getNecessaryActions(): ?array
    {
        return $this->necessaryActions;
    }
}
