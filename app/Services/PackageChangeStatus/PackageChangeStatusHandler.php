<?php

declare(strict_types=1);

namespace App\Services\PackageChangeStatus;

use App\Enums\PackageStatusEnum;
use App\Models\Package;
use App\Services\PackageChangeStatus\Exceptions\ChangePackageStatusException;
use Log;

/**
 * Класс отвечает за изменение статуса пакета и выбор правильного класса
 * для реализации этого изменения в зависимости от типа пакета и нового статуса.
 * Этот класс используется для обеспечения изменения статуса пакета в соответствии с его типом и новым статусом.
 *
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class PackageChangeStatusHandler
{
    /**
     * @var Package
     */
    protected Package $package;

    /**
     * @var int
     */
    protected int $newStatus;

    /**
     * @var bool
     */
    protected bool $isAutoChange;

    /**
     * @param Package  $package
     * @param int  $newStatus
     * @param bool  $isAutoChange
     */
    public function __construct(Package $package, int $newStatus, bool $isAutoChange = false)
    {
        $this->package = $package;
        $this->newStatus = $newStatus;
        $this->isAutoChange = $isAutoChange;
    }

    /**
     * @throws \App\Services\PackageChangeStatus\Exceptions\ChangePackageStatusException
     */
    public function handle(): int
    {
        $actionClassInstance = $this->getActionInstance($this->package, $this->isAutoChange);
        $actionClassInstance->updateStatus();

        return $this->newStatus;
    }

    /**
     * Ищет класс реализующий интерфейс PackageChangeStatusActionInterface по типу пакета
     *
     * @param \App\Models\Package  $package
     * @param bool  $isAutoChange
     *
     * @return \App\Services\PackageChangeStatus\PackageChangeStatusActionInterface
     * @throws \App\Services\PackageChangeStatus\Exceptions\ChangePackageStatusException
     */
    public function getActionInstance(Package $package, bool $isAutoChange): PackageChangeStatusActionInterface
    {
        $packageType = $this->toCamelCase($this->package->type);
        $statusName = $this->toCamelCase(PackageStatusEnum::getKeyByValue($this->newStatus));

        $packageServiceFactoryNamespace = "App\Services\PackageServicesFactory\Catalog";
        $packageTypeActionsNamespace = "\\$packageType\Actions";
        $toStatusAction = "\\To{$statusName}Action";

        $className = $packageServiceFactoryNamespace . $packageTypeActionsNamespace . $toStatusAction;

        if (class_exists($className)) {
            return new $className($package, $isAutoChange);
        }

        Log::error("PackageChangeStatusService: не объявлен класс $className", [
            'packageType' => $packageType,
        ]);

        throw new ChangePackageStatusException();
    }

    /**
     * Переработка констант в CamelCase исключая подчеркивания
     *
     * @param string  $value
     *
     * @return string
     */
    private function toCamelCase(string $value): string
    {
        return str_replace('_', '', ucwords(strtolower($value), '_'));
    }
}
