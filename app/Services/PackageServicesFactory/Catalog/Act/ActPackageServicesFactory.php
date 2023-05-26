<?php

declare(strict_types=1);

namespace App\Services\PackageServicesFactory\Catalog\Act;

use App\Models\Package;
use App\Services\DocumentsService\PackageInterface;
use App\Services\PackageAvailableStatus\PackageAvailableStatusAbstract;
use App\Services\PackageServicesFactory\Catalog\Act\PackageAvailableStatus\ActAvailableStatus;
use App\Services\PackageServicesFactory\Catalog\Act\PackageOfDocuments\ActPackage;
use App\Services\PackageServicesFactory\PackageServicesFactoryInterface;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class ActPackageServicesFactory implements PackageServicesFactoryInterface
{
    /** @inheritDoc */
    public function createPackageOfDocumentsService(
        Package $package = null,
        array $attributes = []
    ): PackageInterface {
        return new ActPackage($package, $attributes);
    }

    /** @inheritDoc */
    public function createPackageAvailableStatusesService(Package $package): PackageAvailableStatusAbstract
    {
        return new ActAvailableStatus($package);
    }

    /** @inheritDoc */
    public function getClassAvailableStatusesService(): string
    {
        return ActAvailableStatus::class;
    }
}
