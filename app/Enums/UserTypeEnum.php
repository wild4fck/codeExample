<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Типы пользователей.
 *
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class UserTypeEnum extends AbstractEnum
{
    /** Тип пользователя - Сотрудник банка */
    public const BANK_EMPLOYEE = 'BANK_EMPLOYEE';
    
    /** Тип пользователя - Агент */
    public const AGENT = 'AGENT';
    
    public static array $texts = [
        self::BANK_EMPLOYEE => 'Сотрудник банка',
        self::AGENT => 'Агент',
    ];
}
