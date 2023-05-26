<?php

declare(strict_types=1);

namespace App\Services\PackageChangeStatus\BaseAction;

use App\Enums\PackageStatusEnum;
use App\Enums\UserTypeEnum;
use App\Services\PackageChangeStatus\PackageChangeStatusActionAbstract;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
abstract class ToVerificationActionAbstract extends PackageChangeStatusActionAbstract
{
    /** @inheritDoc */
    protected function validate(): void
    {
        $this->checkNeededDocuments();
        $this->checkNeededToSign(UserTypeEnum::AGENT);
    }

    /** @inheritDoc */
    public static function getStatus(): int
    {
        return PackageStatusEnum::VERIFICATION;
    }

    /** @inheritDoc */
    public static function getAvailableFromForBankEmployee(): array
    {
        return [];
    }

    /** @inheritDoc */
    public static function getActionTitles(): array|null
    {
        return [
            PackageStatusEnum::APPROVAL => 'Передать на проверку',
        ];
    }

    /** @inheritDoc */
    public static function getActionMessages(): array|string|null
    {
        return 'Передано на проверку';
    }
}
