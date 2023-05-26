<?php

declare(strict_types=1);

namespace App\Services\PackageAvailableStatus;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
interface PackageAvailableStatusInterface
{
    /**
     * Сформировать и вернуть маршрут статусов с доступными переходами
     *
     * @return array
     */
    public static function getMap(): array;
}
