<?php

declare(strict_types=1);

namespace App\Services\PackageChangeStatus\BaseAction;

use App\Enums\PackageStatusEnum;
use App\Enums\UserTypeEnum;
use App\Services\PackageChangeStatus\PackageChangeStatusActionAbstract;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
abstract class ToApprovalActionAbstract extends PackageChangeStatusActionAbstract
{
    /** @inheritDoc */
    protected function validate(): void
    {
        $this->checkNeededDocuments();
    }

    /** @inheritDoc */
    public static function getStatus(): int
    {
        return PackageStatusEnum::APPROVAL;
    }

    /** @inheritDoc */
    public static function getAvailableFromForAgent(): array
    {
        return [];
    }
}
