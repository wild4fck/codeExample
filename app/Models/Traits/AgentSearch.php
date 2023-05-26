<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Enums\OrganizationFormEnum;
use Illuminate\Database\Eloquent\Builder;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
trait AgentSearch
{
    /**
     * Фильтрация модели по агенту/контактному лицу (для ФЛ)
     * Для правильной работы необходимо предварительно добавить join agents, agents_user, user,
     *
     * @param \Illuminate\Database\Eloquent\Builder  $query
     * @param null|string  $searchString
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByAgent(Builder $query, ?string $searchString): Builder
    {
        if (empty($searchString)) {
            return $query;
        }

        $query->where(function ($subQuery) use ($searchString) {
            $subQuery->whereIn('organization_form', [OrganizationFormEnum::FL, OrganizationFormEnum::IP]);
            $subQuery->where(static function ($subQuery2) use ($searchString) {
                $subQuery2->orWhere('users.email', 'ILIKE', "%{$searchString}%")
                    ->orWhereRaw(
                        "CONCAT(users.lastname, ' ', users.firstname, ' ', users.patronymic) ILIKE ?",
                        ["%$searchString%"]
                    );
            });
        })->orWhere('agents.name', 'ILIKE', "%{$searchString}%")
            ->orWhere('agents.inn', 'LIKE', "%{$searchString}%");

        if (is_numeric($searchString)) {
            $query->orWhere('agents.id', $searchString);
        }

        return $query;
    }
}
