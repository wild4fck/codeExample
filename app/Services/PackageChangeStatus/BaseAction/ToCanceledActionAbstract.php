<?php

declare(strict_types=1);

namespace App\Services\PackageChangeStatus\BaseAction;

use App\Enums\PackageStatusEnum;
use App\Services\PackageChangeStatus\PackageChangeStatusActionAbstract;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
abstract class ToCanceledActionAbstract extends PackageChangeStatusActionAbstract
{
    
    /** @inheritDoc */
    protected function validate(): void
    {
    }
    
    /** @inheritDoc */
    public static function getStatus(): int
    {
        return PackageStatusEnum::CANCELED;
    }
    
    /** @inheritDoc */
    public static function getAvailableFromForAgent(): array
    {
        return [];
    }

    /** @inheritDoc */
    public static function getActionMessages(): array|string|null
    {
        return 'Аннулировано';
    }
}
