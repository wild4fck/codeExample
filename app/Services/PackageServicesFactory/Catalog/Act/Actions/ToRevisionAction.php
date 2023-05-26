<?php

declare(strict_types=1);

namespace App\Services\PackageServicesFactory\Catalog\Act\Actions;

use App\Services\PackageChangeStatus\BaseAction\ToRevisionActionAbstract;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class ToRevisionAction extends ToRevisionActionAbstract
{
    /**
     * @return null|int
     */
    public function updateStatus(): ?int
    {
        $this->notifyUsersAboutPackageStatusChange($this->package->bankUser, true);

        return parent::updateStatus();
    }
}
