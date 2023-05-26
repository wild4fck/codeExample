<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class PackageStatusDirections extends AbstractEnum
{
    /** Тип движения пакета - Назад */
    public const BACKWARD = 'backward';
    
    /** Тип движения пакета - Вперед */
    public const FORWARD = 'forward';
}
