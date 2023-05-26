<?php

declare(strict_types=1);

namespace App\Services\PackageServicesFactory\Catalog\Act\PackageAvailableStatus;

use App\Enums\PackageStatusDirections;
use App\Enums\PackageStatusEnum;
use App\Enums\UserTypeEnum;
use App\Services\PackageAvailableStatus\PackageAvailableStatusAbstract;
use App\Services\PackageServicesFactory\Catalog\Act\Actions\ToApprovalAction;
use App\Services\PackageServicesFactory\Catalog\Act\Actions\ToCanceledAction;
use App\Services\PackageServicesFactory\Catalog\Act\Actions\ToCompletedAction;
use App\Services\PackageServicesFactory\Catalog\Act\Actions\ToRevisionAction;
use App\Services\PackageServicesFactory\Catalog\Act\Actions\ToVerificationAction;
use Auth;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class ActAvailableStatus extends PackageAvailableStatusAbstract
{
    /** * @inheritDoc */
    public static function getMap(): array
    {
        $map = [
            PackageStatusEnum::DRAFT => [
                'actions' => [
                    ToCanceledAction::class => PackageStatusDirections::BACKWARD,
                    ToApprovalAction::class => PackageStatusDirections::FORWARD,
                ],
            ],
            PackageStatusEnum::APPROVAL => [
                'userType' => [
                    UserTypeEnum::AGENT,
                ],
                'actions' => [
                    ToRevisionAction::class => PackageStatusDirections::BACKWARD,
                    ToVerificationAction::class => PackageStatusDirections::FORWARD,
                ],
            ],
            PackageStatusEnum::REVISION => [
                'actions' => [
                    ToCanceledAction::class => PackageStatusDirections::BACKWARD,
                    ToApprovalAction::class => PackageStatusDirections::FORWARD,
                ],
            ],
            PackageStatusEnum::VERIFICATION => [
                'actions' => [
                    ToApprovalAction::class => PackageStatusDirections::BACKWARD,
                    ToCompletedAction::class => PackageStatusDirections::FORWARD,
                ],
            ],
            PackageStatusEnum::CANCELED => [
                'actions' => [],
            ],
            PackageStatusEnum::COMPLETED => [
                'actions' => [],
            ],
        ];

        $user = Auth::user();
        if ($user && $user->can('documents.can_cancel_from_completed')) {
            $map[PackageStatusEnum::COMPLETED]['actions'][ToCanceledAction::class] = PackageStatusDirections::BACKWARD;
        }

        return $map;
    }
}
