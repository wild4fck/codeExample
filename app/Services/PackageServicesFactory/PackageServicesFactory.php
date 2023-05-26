<?php

declare(strict_types=1);

namespace App\Services\PackageServicesFactory;

use App\Enums\PackageTypeEnum;
use App\Exceptions\NoPackageTypeException;
use App\Services\PackageServicesFactory\Catalog\Act\ActPackageServicesFactory;

/**
 * Фабрика сервисов на основе типа пакета
 *
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class PackageServicesFactory
{
    /**
     * @param string  $packageType
     *
     * @return PackageServicesFactoryInterface
     * @throws NoPackageTypeException
     */
    public static function getServices(string $packageType): PackageServicesFactoryInterface
    {
        return match ($packageType) {
            PackageTypeEnum::ACT => new ActPackageServicesFactory(),
            default => throw new NoPackageTypeException()
        };
    }
}
