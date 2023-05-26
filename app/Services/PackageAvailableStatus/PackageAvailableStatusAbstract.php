<?php

declare(strict_types=1);

namespace App\Services\PackageAvailableStatus;

use App\Enums\PackageStatusDirections;
use App\Enums\PackageStatusEnum;
use App\Models\Package;
use App\Models\User;
use App\Services\PackageChangeStatus\Exceptions\PackageStatusValidateException;
use App\Services\PackageChangeStatus\PackageChangeStatusActionAbstract;
use App\Services\PackageChangeStatus\PackageChangeStatusActionInterface;
use Exception;
use Illuminate\Support\Collection;

/**
 * Абстрактный класс формирования доступных статусов пакета
 *
 * @author Wild4fck <wild4fck@yandex.ru>
 */
abstract class PackageAvailableStatusAbstract implements PackageAvailableStatusInterface
{
    /**
     * Карта доступных экшенов по статусам
     */
    protected static array $map = [];
    
    /**
     * Пакет документов, в котором мы проверяем доступные статусы
     *
     * @var \App\Models\Package
     */
    protected Package $package;
    
    /**
     * @var array
     */
    private array $availableStatuses = [];
    
    /**
     * @param Package  $package
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
    }
    
    /**
     * @return array
     */
    public static function getMap(): array
    {
        return static::$map;
    }
    
    /**
     * Получение списка статусов доступных в пакете
     *
     * @return array
     */
    public static function getPackageStatuses(): array
    {
        return array_keys(static::getMap());
    }
    
    /**
     * Тут мы берем карту доступных статусов (actions)
     * и проверяем если валидация не отваливает action,
     * то его статус проходит как доступный
     *
     * @param \App\Models\User  $user
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableStatuses(User $user): Collection
    {
        if (!$this->userHasAccessToAction($this->package, $user)) {
            return collect($this->availableStatuses);
        }
   
        collect(static::getMap()[$this->package->status_id]['actions'])->each(
            function (string $direction, string $actionClass) use ($user) {
                try {
                    if (!$this->isActionAvailableFromForUser($actionClass, $user)) {
                        return;
                    }
                    
                    new $actionClass($this->package);
                    
                    $this->putToAvailableStatuses($actionClass, $direction);
                } catch (PackageStatusValidateException $exception) {
                    $this->putToAvailableStatuses(
                        $actionClass,
                        $direction,
                        false,
                        $exception->getMessage(),
                        $exception->getNecessaryActions() ?? []
                    );
                }
            }
        );
        
        return collect($this->availableStatuses);
    }
    
    /**
     * Проверяем доступ клиента согласно маппингу к экшенам
     *
     * @param \App\Models\Package  $package
     * @param \App\Models\User  $user
     *
     * @return bool
     */
    private function userHasAccessToAction(Package $package, User $user): bool
    {
        $status = $package->status_id;
        if (!$this->userCanAccessToStatus($status, $user)) {
            return false;
        }
        
        if (isset(static::getMap()[$status]['permissions']) && User::isUserBankEmployee($user)) {
            return collect(static::getMap()[$status]['permissions'])
                ->every(fn($permissions) => $user->can($permissions));
        }
        
        if (isset(static::getMap()[$status]['userType'])) {
            return in_array($user->type, static::getMap()[$status]['userType'], true);
        }
        
        return true;
    }
    
    /**
     * Проверка доступа пользователя к статусу
     *
     * @param int  $status
     * @param \App\Models\User  $user
     *
     * @return bool
     */
    private function userCanAccessToStatus(int $status, User $user): bool
    {
        if (User::isUserAgent($user)) {
            return true;
        }
        
        $statusName = PackageStatusEnum::getNameById($status);
        return $user->can("statuses.{$this->package->type}.{$statusName}");
    }
    
    /**
     * Доступен ли переход для пользователя их текущего статуса
     *
     * @param string  $actionClass
     * @param \App\Models\User  $user
     *
     * @return bool
     */
    private function isActionAvailableFromForUser(string $actionClass, User $user): bool
    {
        /** @var PackageChangeStatusActionInterface $actionClass */
        if (User::isUserAgent($user)
            && !in_array($this->package->status_id, $actionClass::getAvailableFromForAgent(), true)
        ) {
            return false;
        }
        
        if (User::isUserBankEmployee($user)
            && !in_array(
                $this->package->status_id,
                $actionClass::getAvailableFromForBankEmployee(),
                true
            )) {
            return false;
        }
        
        return true;
    }
    
    /**
     * @param string  $actionClass
     * @param string  $direction
     * @param bool  $available
     * @param string  $message
     * @param array  $necessaryActions
     *
     * @return void
     */
    private function putToAvailableStatuses(
        string $actionClass,
        string $direction,
        bool $available = true,
        string $message = '',
        array $necessaryActions = []
    ): void {
        /** @var PackageChangeStatusActionInterface  $actionClass */
        $statusName = PackageStatusEnum::getNameById($actionClass::getStatus());
        
        $this->availableStatuses[$actionClass::getStatus()] = [
            'id' => $actionClass::getStatus(),
            'name' => $statusName,
            'direction' => $direction,
            'actionTitle' => $actionClass::getActionTitle($this->package->status_id),
            'actionMessage' => $actionClass::getActionMessage($this->package->status_id),
            'title' => PackageStatusEnum::getText($actionClass::getStatus()),
            'available' => $available,
            'message' => $message,
            'necessaryActions' => $necessaryActions,
        ];
    }
    
    /**
     * Получение списка доступных действий движения вперед по статусам для пользователя
     *
     * @param \App\Models\User  $user
     *
     * @return array
     */
    public function getForwardAvailableActions(User $user): array
    {
        if (!$this->userHasAccessToAction($this->package, $user)) {
            return [];
        }
        
        return collect(static::getMap()[$this->package->status_id]['actions'])
            ->filter(
                function (string $direction, string $actionClass) use ($user) {
                    return $direction === PackageStatusDirections::FORWARD
                        || !$this->isActionAvailableFromForUser($actionClass, $user);
                }
            )->map(function (string $direction, string $actionClass) use ($user) {
                
                /** @var PackageChangeStatusActionInterface $actionClass */
                $actionInfo = [
                    'actionTitle' => $actionClass::getActionTitle($this->package->status_id),
                    'messages' => [],
                ];
                
                try {
                    (new $actionClass($this->package, false, $user));
                } catch (Exception $exception) {
                    $actionInfo['messages'] = explode("\n", $exception->getMessage());
                }
                
                return $actionInfo;
            })
            ->toArray();
    }
}
