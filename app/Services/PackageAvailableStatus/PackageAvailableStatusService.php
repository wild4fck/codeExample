<?php

declare(strict_types=1);

namespace App\Services\PackageAvailableStatus;

use App\Enums\PackageStatusDirections;
use App\Enums\PackageStatusEnum;
use App\Models\Package;
use App\Models\User;
use App\Services\PackageServicesFactory\PackageServicesFactory;

/**
 * Сервис работы с доступными/недоступными статусами
 * todo: рассмотреть необходимость переделки со статических методов на объектную модель
 *
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class PackageAvailableStatusService
{
    /**
     * Справочник статусов с пометкой доступности для пользователя
     *
     * @return array
     */
    public static function getStatusesForUser(User $user): array
    {
        if (User::isUserAgent($user)) {
            return array_keys(PackageStatusEnum::toArray());
        }

        return self::getBankEmployeeUserStatusesNames($user);
    }

    /**
     * @return array
     */
    public static function getBankEmployeeUserStatusesNames(User $user): array
    {
        return $user->getAllPermissions()
            ->filter(fn($item) => str_starts_with($item['name'], 'statuses.'))
            ->pluck('name')
            ->map(fn($permission) => preg_replace('/statuses\.[\w_]+\.([\w_]+)/m', '$1', $permission)) // statuses.ACT.VERIFICATION => VERIFICATION
            ->toArray();
    }

    /**
     * @param \App\Models\Package  $package
     * @param \App\Models\User  $user
     *
     * @return array
     * @throws \App\Exceptions\NoPackageTypeException
     */
    public static function getAvailableStatuses(Package $package, User $user): array
    {
        return PackageServicesFactory::getServices($package->type)
            ->createPackageAvailableStatusesService($package)
            ->getAvailableStatuses($user)->toArray();
    }

    /**
     * @param \App\Models\Package  $package
     * @param \App\Models\User  $user
     *
     * @return null[]
     * @throws \App\Exceptions\NoPackageTypeException
     */
    public static function getAvailablePackageActions(Package $package, User $user): array
    {
        $actions = PackageServicesFactory::getServices($package->type)
            ->createPackageAvailableStatusesService($package)
            ->getAvailableStatuses($user);

        $actionsMessages = [
            'statusChange' => null,
            'needProcess' => null,
        ];
        if (!$actions->isEmpty()) {
            $actionsMessages['statusChange'] = $actions->first(
                fn($item) =>
                    $item['available'] === true
                    && $item['direction'] === PackageStatusDirections::FORWARD
            );
            if ($actionsMessages['statusChange']) {
                $actionsMessages['statusChange'] = !empty($actionsMessages['statusChange']['message'])
                    ? $actionsMessages['statusChange']['message']
                    : 'Доступна смена статуса';
            }
            $actionsMessages['needProcess'] = $actions
                ->first(fn($item) => $item['available'] === false)['message'] ?? null;
        }

        return $actionsMessages;
    }

    /**
     * @param \App\Models\Package  $package
     * @param \App\Models\User  $user
     *
     * @return array
     * @throws \App\Exceptions\NoPackageTypeException
     */
    public static function getForwardAvailableActions(Package $package, User $user): array
    {
        return PackageServicesFactory::getServices($package->type)
            ->createPackageAvailableStatusesService($package)
            ->getForwardAvailableActions($user);
    }
}
