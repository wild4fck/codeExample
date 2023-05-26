<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Статусы пакета документов
 *
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class PackageStatusEnum extends AbstractEnum
{
    /** @var int Статуса пакета - Черновик */
    public const DRAFT = 1;
    
    /** @var int Статуса пакета - Согласование */
    public const APPROVAL = 2;
    
    /** @var int Статуса пакета - Возврат на доработку */
    public const REVISION = 3;
    
    /** @var int Статус пакета - Верификация */
    public const VERIFICATION = 4;
    
    /** @var int Статуса пакета - Аннулирован */
    public const CANCELED = 5;
    
    /** @var int Статуса пакета - Документооборот завершен */
    public const COMPLETED = 6;
    
    public static array $texts = [
        self::DRAFT => 'Черновик',
        self::APPROVAL => 'Согласование',
        self::REVISION => 'Возврат на доработку',
        self::VERIFICATION => 'Верификация',
        self::CANCELED => 'Аннулирован',
        self::COMPLETED => 'Документооборот завершен',
    ];
}
