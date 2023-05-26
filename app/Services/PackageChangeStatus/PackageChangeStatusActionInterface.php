<?php

declare(strict_types=1);

namespace App\Services\PackageChangeStatus;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
interface PackageChangeStatusActionInterface
{
    /**
     * Статус перехода (Статус, переход на который выполняет текущий Action)
     * @return int
     */
    public static function getStatus(): int;
    
    /**
     * Из каких статусов может выполнять переход сотрудник платформы
     * @return array
     */
    public static function getAvailableFromForBankEmployee(): array;
    
    /**
     * Из каких статусов может выполнить переход клиент
     * @return array
     */
    public static function getAvailableFromForAgent(): array;
    
    /**
     * Мапинг статусов и названий кнопок
     * @return null|array
     */
    public static function getActionTitles(): array|null;
    
    /**
     * Сообщение о смене статуса
     * @return null|array|string
     */
    public static function getActionMessages(): array|string|null;
    
    /**
     * Статус на который необходим автопереход (по условию например)
     * @return null|int
     */
    public function getStatusAfter(): ?int;
    
    /**
     * Действие при переходе в статус
     * @return null|int
     */
    public function updateStatus(): ?int;
}
