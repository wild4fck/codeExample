<?php

declare(strict_types=1);

namespace App\Services\PackageChangeStatus\BaseAction;

use App\Enums\PackageStatusEnum;
use App\Enums\UserTypeEnum;
use App\Services\PackageChangeStatus\PackageChangeStatusActionAbstract;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
abstract class ToCompletedActionAbstract extends PackageChangeStatusActionAbstract
{

    /** @inheritDoc */
    protected function validate(): void
    {
        $this->checkNeededToSign(UserTypeEnum::BANK_EMPLOYEE);
    }

    /** @inheritDoc */
    public static function getStatus(): int
    {
        return PackageStatusEnum::COMPLETED;
    }

    /** @inheritDoc */
    public static function getAvailableFromForAgent(): array
    {
        return [];
    }

    /** @inheritDoc */
    public static function getActionTitles(): array|null
    {
        return [
            PackageStatusEnum::VERIFICATION => 'Завершить работу с пакетом',
        ];
    }

    /** @inheritDoc */
    public static function getActionMessages(): array|string|null
    {
        return 'Документооборот завершён';
    }
}
