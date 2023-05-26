<?php

declare(strict_types=1);

namespace App\Services\PackageChangeStatus;

use App\Enums\LoggerDataTypeEnum;
use App\Models\Package;
use App\Models\PackageLogger;
use App\Services\PackageChangeStatus\Exceptions\ChangePackageStatusException;
use App\Services\PackageChangeStatus\Exceptions\ChangeSameStatusException;
use Exception;

/**
 * Сервис, отвечающий за смену статуса в пакете документов
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class ChangePackageStatusService
{
    /**
     * Пакет, в котором изменяется статус
     *
     * @var \App\Models\Package
     */
    private Package $package;
    
    /**
     * Id нового статуса
     *
     * @var int
     */
    private int $newStatus;
    
    /**
     * Автоматическое или ручное обновление
     *
     * @var bool
     */
    private bool $isAutoChange;
    
    /**
     * Нужно ли сохранять статус пакета
     *
     * @var bool
     */
    private bool $savePackage;
    
    /**
     * Статус на который должен быть выполнен ещё один переход после текущего
     *
     * @var null|int
     */
    private ?int $statusAfter;
    
    /**
     * @param \App\Models\Package  $package  Пакет документов, в котором меняем статус
     * @param int  $newStatus  Новый статус
     * @param bool  $isAutoChange  Автоматическое изменение статуса или нет
     * @param bool  $savePackage  Сохранять ли изменения пакета
     */
    public function __construct(Package $package, int $newStatus, bool $isAutoChange = false, bool $savePackage = true)
    {
        $this->package = $package;
        $this->newStatus = $newStatus;
        $this->isAutoChange = $isAutoChange;
        $this->savePackage = $savePackage;
    }
    
    /**
     * @return int
     *
     * @throws \App\Services\PackageChangeStatus\Exceptions\ChangePackageStatusException
     */
    public function changeStatus(): int
    {
        try {
            return $this->changePackageStatus();
        } catch (Exception $exception) {
            // Если меняется статус на текущий, то не будем выбрасывать исключение, а просто вернём текущий статус
            if ($exception->getCode() === (new ChangeSameStatusException())->getCode()) {
                return $this->newStatus;
            }
            throw new ChangePackageStatusException($exception->getMessage(), (int)$exception->getCode(), $exception);
        }
    }
    
    /**
     * @return int
     * @throws \App\Services\PackageChangeStatus\Exceptions\ChangeSameStatusException
     * @throws \App\Services\PackageChangeStatus\Exceptions\NotMappedActionException
     * @throws \Exception
     */
    private function changePackageStatus(): int
    {
        $this->validateUpdate();
        $this->executeUpdate();
        $this->saveUpdate();
        $this->updateStatusIfNeeded();
        
        return $this->newStatus;
    }
    
    /**
     * Валидация на случай перехода в текущий статус
     *
     * @return void
     * @throws \App\Services\PackageChangeStatus\Exceptions\ChangeSameStatusException
     */
    private function validateUpdate(): void
    {
        if ($this->package->status_id === $this->newStatus) {
            throw new ChangeSameStatusException();
        }
    }
    
    /**
     * Определение логики смены статуса по типу пакета документов
     *
     * @return void
     * @throws Exception
     */
    private function executeUpdate(): void
    {
        // Если null, значит будет переход на $this->newStatus
        $this->statusAfter = (new PackageChangeStatusHandler($this->package, $this->newStatus, $this->isAutoChange))
            ->handle();
    }
    
    /**
     * Сохранение нового статуса в модель пакета документов
     *
     * @return void
     */
    private function saveUpdate(): void
    {
        $this->package->status_id = $this->newStatus;
        
        // Сохраняем изменения пакета
        if ($this->savePackage) {
            $this->package->save();
        }
    }
    
    /**
     * Логирование смены статуса
     *
     * @param string  $lastStatus
     *
     * @return void
     */
    private function loggingUpdate(string $lastStatus): void
    {
        PackageLogger::add(
            $this->package,
            'status',
            $lastStatus,
            $this->newStatus,
            LoggerDataTypeEnum::DATA_TYPE_NUMBER,
            null,
            $this->isAutoChange
        );
    }
    
    /**
     * Если нужно перейти на другой статус после смены - запускаем петлю
     *
     * @return void
     * @throws \App\Services\PackageChangeStatus\Exceptions\ChangeSameStatusException
     * @throws \App\Services\PackageChangeStatus\Exceptions\NotMappedActionException
     */
    private function updateStatusIfNeeded(): void
    {
        if (!empty($this->statusAfter)) {
            $this->newStatus = $this->statusAfter;
            $this->isAutoChange = true;
            $this->newStatus = $this->changePackageStatus();
        }
    }
}
