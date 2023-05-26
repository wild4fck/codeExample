<?php

declare(strict_types=1);

namespace App\Services\PackageServicesFactory;

use App\Models\Package;
use App\Services\DocumentsService\PackageInterface;
use App\Services\PackageAvailableStatus\PackageAvailableStatusAbstract;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
interface PackageServicesFactoryInterface
{
    /**
     * Получаем сервис PackageDocumentsService
     *
     * @param null|\App\Models\Package  $package
     * @param array  $attributes
     *
     * @return \App\Services\DocumentsService\PackageInterface
     */
    public function createPackageOfDocumentsService(
        ?Package $package = null,
        array $attributes = []
    ): PackageInterface;

    /**
     * Получаем сервис AvailableStatuses
     *
     * @param \App\Models\Package  $package
     *
     * @return \App\Services\PackageAvailableStatus\PackageAvailableStatusAbstract
     */
    public function createPackageAvailableStatusesService(Package $package): PackageAvailableStatusAbstract;

    /**
     * @return class-string<\App\Services\PackageAvailableStatus\PackageAvailableStatusAbstract>
     */
    public function getClassAvailableStatusesService(): string;
}
