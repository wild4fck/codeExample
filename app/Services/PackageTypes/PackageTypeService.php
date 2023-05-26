<?php

declare(strict_types=1);

namespace App\Services\PackageTypes;

use App\Enums\PackageTypeEnum;
use App\Models\Package;
use App\Models\User;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class PackageTypeService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Справочник статусов с пометкой доступности для пользователя
     * todo: оценить необходимость проверки пользователя на авторизованность
     *
     * @return array
     */
    public function getTypesForUser(): array
    {
        if (User::isUserAgent($this->user)) {
            $hasAccess = $this->getAgentAccessTypes();
            $canUpload = $this->getAgentCanUploadTypes();
        } else {
            $hasAccess = $this->getBankAccessTypes();
            $canUpload = $this->getBankCanUploadTypes();
        }

        return [
            'hasAccess' => $hasAccess,
            'canUpload' => $canUpload,
        ];
    }

    /**
     * Получение списка типов существующих пакетов для Агентов
     *
     * @return array
     */
    public function getAgentAccessTypes(): array
    {
        if (!$this->user->agent->isActive()) {
            return [];
        }
        return Package::query()
            ->distinct()
            ->select('type')
            ->where('agent_id', $this->user->agent->id)
            ->get()
            ->pluck('type')
            ->toArray();
    }

    /**
     * Получение списка типов пакетов для Агента, которые он может загрузить
     *
     * @return array
     */
    public function getAgentCanUploadTypes(): array
    {
        if (!$this->user->agent->isActive()) {
            return [];
        }
        return [
            PackageTypeEnum::ACT,
        ];
    }

    /**
     * Получение списка типов доступных пакетов для Банковских сотрудников
     *
     * @return array
     */
    public function getBankAccessTypes(): array
    {
        return $this->user->getAllPermissions()
            ->filter(fn($item) => str_starts_with($item['name'], 'statuses.'))
            ->map(fn($permission) => preg_replace('/statuses\.([\w_]+)\.[\w_]+/m', '$1', $permission['name']))
            ->unique()
            ->toArray();
    }

    /**
     * Получение списка типов пакетов для Персонала, которые он может загрузить
     *
     * @return array
     */
    public function getBankCanUploadTypes(): array
    {
        // todo: а нужен ли этот список
        /*$canUploadPermissions = $this->canUploadPermissionsList()
            ->map(
                fn($types, $type) => collect($types)
                    ->map(fn($depart) => "{$type}\.{$depart}")
                    ->implode('|')
            )->implode('|');*/

        return $this->user->getAllPermissions()
            // TODO: так как у нас нет департаментов, то сотрудники банка могут грузить все типы документов без раздлеления??
            //->filter(fn($item) => preg_match("/^departments\.({$canUploadPermissions})/", $item['name']))
            //->map(fn($permission) => preg_replace('/departments\.([\w_]+)\.[\w_]+/m', '$1', $permission['name']))
            ->unique()
            ->toArray();
    }

    /**
     * Список разрешений для создания пакета персоналом
     * todo: а нужен ли этот список?
     *
     * @return \Illuminate\Support\Collection
     */
    public function canUploadPermissionsList(): Collection
    {
        // TODOC Добавление нового пакета
        // TODO нужно сделать явное обозначение возможности загружать тот или иной тип пакета, потому что везде приходится мастерить такой костыль
        /*return collect([
            PackageTypeEnum::ACT => [
                DepartmentEnum::ANALYTICS,
                DepartmentEnum::ADMINISTRATION,
            ],
            PackageTypeEnum::AGENT_CONTRACT => [
                DepartmentEnum::ADMINISTRATION,
            ],
            PackageTypeEnum::SUPPLEMENTARY_AGREEMENT => [
                DepartmentEnum::ADMINISTRATION,
            ],
            PackageTypeEnum::ARBITRARY => [
                DepartmentEnum::ADMINISTRATION,
            ],
            PackageTypeEnum::ACT_RECONCILIATION => [
                DepartmentEnum::ACCOUNTING,
            ],
        ]);*/

        return collect();
    }
}
