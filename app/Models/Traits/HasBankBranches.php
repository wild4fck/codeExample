<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\BankBranch;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
trait HasBankBranches
{
    /**
     * Отделения банков
     *
     * @return BelongsToMany
     */
    public function bankBranches(): BelongsToMany
    {
        return $this->morphToMany(
            BankBranch::class,
            'model',
            'model_has_bank_branches',
            'model_id',
            'bank_branch_id'
        );
    }

    /**
     * Фильтрация пользователей по банковскому отделению текущего пользователя
     *
     * @param \Illuminate\Database\Eloquent\Builder  $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByOwnBankBranch(Builder $query): Builder
    {
        $user = Auth::user();

        if ($user && $user->can('bank_branches.own_only')) {
            return $query->whereHas('bankBranches', function (Builder $subQuery) use ($user) {
                $subQuery->whereIn(
                    'bank_branches.id',
                    $user->bankBranches->pluck('id')
                );
            });
        }

        return $query;
    }
}
