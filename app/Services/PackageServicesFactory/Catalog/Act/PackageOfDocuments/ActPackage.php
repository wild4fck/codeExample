<?php

declare(strict_types=1);

namespace App\Services\PackageServicesFactory\Catalog\Act\PackageOfDocuments;

use App\Enums\OrganizationFormEnum;
use App\Enums\PackageStatusEnum;
use App\Services\DocumentsService\PackageOfDocuments\PackageAbstract;

class ActPackage extends PackageAbstract
{
    /** @inheritDoc */
    public function make(): array
    {
        $package = [
            ActPackageDocument::docAct(),
            ActPackageDocument::docActEditable(),
        ];

        if ($this->package && $this->canShowBill()) {
            $package[] = ActPackageDocument::docBill()->setIsRequired($this->isBillRequired());
        }

        return $package;
    }

    /** * @inheritDoc */
    public function makeWithoutConditions(): array
    {
        return [
            ActPackageDocument::docAct(),
            ActPackageDocument::docActEditable(),
            ActPackageDocument::docBill()->setIsRequired($this->isBillRequired())
        ];
    }

    /**
     * Показывать счет если статус "Согласование" или документ загружен
     */
    protected function canShowBill(): bool
    {
        $docBill = $this->getCollection(false)
            ->fillFromDatabase()
            ->getDocumentBySlot(ActPackageDocument::docBill()->slot);

        return $this->package->status_id === PackageStatusEnum::APPROVAL
            || ($docBill && $docBill->uploaded);
    }

    /**
     * @return bool
     */
    private function isBillRequired(): bool
    {
        return isset($this->package)
            && $this->package->agent->organization_form !== OrganizationFormEnum::FL;
    }
}
