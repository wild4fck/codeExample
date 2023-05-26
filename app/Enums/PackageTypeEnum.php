<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Типы пакетов документов.
 *
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class PackageTypeEnum extends AbstractEnum
{
    /** @var string Тип пакета - Закрывающие документы */
    public const ACT = 'ACT';

    /**
     * @var array
     */
    public static array $texts = [
        self::ACT => 'Закрывающие документы',
    ];
}
