<?php

declare(strict_types=1);

namespace App\Services\PackageChangeStatus\BaseAction;

use App\Enums\PackageStatusEnum;
use App\Services\PackageChangeStatus\PackageChangeStatusActionAbstract;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
abstract class ToRevisionActionAbstract extends PackageChangeStatusActionAbstract
{
    /** @inheritDoc */
    protected function validate(): void
    {
    }
    
    /** @inheritDoc */
    public static function getStatus(): int
    {
        return PackageStatusEnum::REVISION;
    }
    
    /** @inheritDoc */
    public static function getAvailableFromForBankEmployee(): array
    {
        return [];
    }
    
    /** @inheritDoc */
    public static function getAvailableFromForAgent(): array
    {
        return [
            PackageStatusEnum::APPROVAL,
        ];
    }
    
    /** @inheritDoc */
    public static function getActionTitles(): array|null
    {
        return [
            PackageStatusEnum::APPROVAL => 'Вернуть на доработку',
        ];
    }
    
    /** @inheritDoc */
    public static function getActionMessages(): array|string|null
    {
        return 'Передано на доработку';
    }
}
