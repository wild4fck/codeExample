<?php

declare(strict_types=1);

namespace App\Services\PackageServicesFactory\Catalog\Act\Actions;

use App\Enums\PackageStatusEnum;
use App\Services\PackageChangeStatus\BaseAction\ToCompletedActionAbstract;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class ToCompletedAction extends ToCompletedActionAbstract
{
    /** @inheritDoc */
    public static function getAvailableFromForBankEmployee(): array
    {
        return [
            PackageStatusEnum::VERIFICATION,
        ];
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
