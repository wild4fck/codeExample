<?php

declare(strict_types=1);

namespace App\Services\PackageServicesFactory\Catalog\Act\Actions;

use App\Enums\PackageStatusEnum;
use App\Services\PackageChangeStatus\BaseAction\ToCanceledActionAbstract;
use Auth;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class ToCanceledAction extends ToCanceledActionAbstract
{
    /** @inheritDoc */
    public static function getActionTitles(): array|null
    {
        return [
            PackageStatusEnum::DRAFT => 'Аннулировать',
            PackageStatusEnum::REVISION => 'Аннулировать',
            PackageStatusEnum::COMPLETED => 'Аннулировать',
        ];
    }

    /** @inheritDoc */
    public static function getAvailableFromForBankEmployee(): array
    {
        $availableFrom = [
            PackageStatusEnum::DRAFT,
            PackageStatusEnum::REVISION,
        ];

        /** @noinspection NullPointerExceptionInspection */
        if (Auth::user()->can('documents.can_cancel_from_completed')) {
            $availableFrom[] = PackageStatusEnum::COMPLETED;
        }

        return $availableFrom;
    }
}
