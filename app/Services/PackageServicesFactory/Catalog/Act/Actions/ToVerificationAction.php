<?php

declare(strict_types=1);

namespace App\Services\PackageServicesFactory\Catalog\Act\Actions;

use App\Enums\PackageStatusEnum;
use App\Services\PackageChangeStatus\BaseAction\ToVerificationActionAbstract;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class ToVerificationAction extends ToVerificationActionAbstract
{
    /** @inheritDoc */
    public static function getAvailableFromForAgent(): array
    {
        return [
            PackageStatusEnum::APPROVAL,
        ];
    }

    /**
     * @return null|int
     */
    public function updateStatus(): ?int
    {
        $this->notifyUsersAboutPackageStatusChange($this->package->bankUser, true);

        return parent::updateStatus();
    }
}
