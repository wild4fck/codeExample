<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

/**
 * Трейт использует трейты Spatie HasPermissions и HasRoles
 * Можно использовать только его, вместо трейтов Spatie
 * @author Wild4fck <wild4fck@yandex.ru>
 */
trait HasPermissions
{
    use HasRoles;

    /**
     * Исключает записи, у которых есть роль с $permissions.
     * Исключает роли с $permissions.
     * Для суперАдмина не исключает.
     *
     * Копия скопа из \Spatie\Permission\Traits\HasPermissions, но доработанный и с обратным эффектом.
     *
     * @param \Illuminate\Database\Eloquent\Builder  $query
     * @param \Illuminate\Support\Collection|array  $permissions
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExcludeByPermissions(Builder $query, Collection|array $permissions): Builder
    {
        $user = Auth::user();

        if ($user && $user->can('extra.admin')) {
            return $query;
        }

        $permissions = $this->convertToPermissionModels($permissions);

        $query->whereDoesntHave('permissions', function (Builder $subQuery) use ($permissions) {
            $permissionClass = $this->getPermissionClass();
            $key = (new $permissionClass())->getKeyName();
            $subQuery->whereIn(
                config('permission.table_names.permissions') . ".$key",
                array_column($permissions, $key)
            );
        });

        if (self::has('roles')) {
            $rolesWithPermissions = array_unique(array_reduce($permissions, static function ($result, $permission) {
                return array_merge($result, $permission->roles->all());
            }, []));

            $query->whereDoesntHave('roles', function (Builder $subQuery) use ($rolesWithPermissions) {
                $roleClass = $this->getRoleClass();
                $key = (new $roleClass())->getKeyName();
                $subQuery->whereIn(
                    config('permission.table_names.roles') . ".$key",
                    array_column($rolesWithPermissions, $key)
                );
            });
        }

        return $query;
    }
}
