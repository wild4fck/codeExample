<?php

declare(strict_types=1);

namespace App\Services\PackageServicesFactory\Catalog\Act\PackageOfDocuments;

use App\Enums\PackageStatusEnum;
use App\Enums\PackageTypeEnum;
use App\Enums\UserTypeEnum;
use App\Services\DocumentsService\PackageOfDocuments\PackageDocument;

class ActPackageDocument extends PackageDocument
{
    /**
     * Акт
     *
     * @return ActPackageDocument
     */
    public static function docAct(): ActPackageDocument
    {
        $packType = PackageTypeEnum::ACT;
        return (new self())
            ->setTitle('Акт')
            ->setSlot('view')
            ->setNeedToSignAgent(true)
            ->setNeedToSignBank(true)
            ->setExtension('pdf')
            ->setIsRequired()
            ->setCommenting()
            ->setEditing([
                UserTypeEnum::BANK_EMPLOYEE => [
                    PackageStatusEnum::DRAFT => [
                        // Проверка прав
                    ],
                    PackageStatusEnum::REVISION => [
                        // Проверка прав
                    ],
                ],
            ])
            ->setSignStatuses([
                UserTypeEnum::BANK_EMPLOYEE => [
                    PackageStatusEnum::VERIFICATION,
                ],
                UserTypeEnum::AGENT => [
                    PackageStatusEnum::APPROVAL,
                ],
            ])
            ->setCanUpload([
                UserTypeEnum::BANK_EMPLOYEE => [
                    PackageStatusEnum::DRAFT => [
                        // Проверка прав
                    ],
                    PackageStatusEnum::REVISION => [
                        // Проверка прав
                    ],
                ],
            ]);
    }

    /**
     * Акт (редактируемый)
     *
     * @return ActPackageDocument
     */
    public static function docActEditable(): ActPackageDocument
    {
        $packType = PackageTypeEnum::ACT;
        return (new self())
            ->setTitle('Акт (редактируемый)')
            ->setSlot('editable')
            ->setNeedToSignAgent(false)
            ->setNeedToSignBank(false)
            ->setExtension('*')
            ->setIsRequired(false)
            ->setCommenting(false)
            ->setHide([UserTypeEnum::AGENT])
            ->setEditing([
                UserTypeEnum::BANK_EMPLOYEE => [
                    PackageStatusEnum::DRAFT => [
                        // Проверка прав
                    ],
                    PackageStatusEnum::REVISION => [
                        // Проверка прав
                    ],
                ],
            ])
            ->setCanUpload([
                UserTypeEnum::BANK_EMPLOYEE => [
                    PackageStatusEnum::DRAFT => [
                        // Проверка прав
                    ],
                    PackageStatusEnum::REVISION => [
                        // Проверка прав
                    ]
                ],
            ]);
    }

    /**
     * Счёт на оплату
     *
     * @return ActPackageDocument
     */
    public static function docBill(): ActPackageDocument
    {
        return (new self())
            ->setTitle('Счёт на оплату')
            ->setSlot('bill')
            ->setNeedToSignAgent(true)
            ->setNeedToSignBank(false)
            ->setExtension('pdf')
            ->setIsRequired(false)
            ->setCommenting()
            ->setCabinet(UserTypeEnum::AGENT)
            ->setEditing([
                UserTypeEnum::AGENT => [
                    PackageStatusEnum::APPROVAL,
                ],
            ])
            ->setSignStatuses([
                UserTypeEnum::AGENT => [
                    PackageStatusEnum::APPROVAL,
                ],
            ])
            ->setCanUpload([
                UserTypeEnum::AGENT => [
                    PackageStatusEnum::APPROVAL,
                ],
            ]);
    }
}
