<?php

declare(strict_types=1);

namespace App\Services\DocumentsService\PackageOfDocuments;

use App\Models\Package;
use App\Services\PackageServicesFactory\PackageServicesFactory;

/**
 * Сервис работы с PackageOfDocuments
 *
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class PackageDocumentsService
{
    /**
     * Сформировать пакет (с необходимыми документами в нем, учитывая статус и т.д.) по пакету или по типу пакета
     *
     * @param \App\Models\Package|null  $package
     * @param array  $attributes
     * @param string|null  $type
     *
     * @return \App\Services\DocumentsService\PackageOfDocuments\PackageDocumentsCollection
     * @throws \App\Exceptions\NoPackageTypeException
     */
    public static function createPackage(
        Package $package = null,
        array $attributes = [],
        string $type = null
    ): PackageDocumentsCollection {
        $packageType = $package->type ?? $type;
        return PackageServicesFactory::getServices($packageType)
            ->createPackageOfDocumentsService($package, $attributes)
            ->getCollection();
    }
    
    /**
     * Получить шаблон пакета документов (какие документы могут быть в пакете) по пакету или по типу пакета
     *
     * @param \App\Models\Package|null  $package
     * @param array  $attributes
     * @param string|null  $type
     *
     * @return \App\Services\DocumentsService\PackageOfDocuments\PackageDocumentsCollection
     * @throws \App\Exceptions\NoPackageTypeException
     */
    public static function getPackageTemplate(
        Package $package = null,
        array $attributes = [],
        string $type = null
    ): PackageDocumentsCollection {
        $packageType = $package->type ?? $type;
        return PackageServicesFactory::getServices($packageType)
            ->createPackageOfDocumentsService($package, $attributes)->getCollection(false);
    }
}
