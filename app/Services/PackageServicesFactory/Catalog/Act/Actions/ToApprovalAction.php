<?php

declare(strict_types=1);

namespace App\Services\PackageServicesFactory\Catalog\Act\Actions;

use App\Enums\PackageStatusEnum;
use App\Services\PackageChangeStatus\BaseAction\ToApprovalActionAbstract;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class ToApprovalAction extends ToApprovalActionAbstract
{
    /** @inheritDoc */
    public static function getAvailableFromForBankEmployee(): array
    {
        return [
            PackageStatusEnum::DRAFT,
            PackageStatusEnum::REVISION,
            PackageStatusEnum::VERIFICATION,
        ];
    }

    /** @inheritDoc */
    public static function getActionTitles(): array
    {
        return [
            PackageStatusEnum::DRAFT => 'Передать агенту на подписание',
            PackageStatusEnum::REVISION => 'Вернуть агенту на подписание',
            PackageStatusEnum::VERIFICATION => 'Вернуть на доработку',
        ];
    }

    /** @inheritDoc */
    public static function getActionMessages(): array|string
    {
        return 'Передано агенту на подписание';
    }

    /**
     * @return null|int
     */
    public function updateStatus(): ?int
    {
        $this->notifyUsersAboutPackageStatusChange($this->package->agent->users->all());

        return parent::updateStatus();
    }
}
