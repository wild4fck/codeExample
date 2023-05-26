<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\BankBranch;
use App\Models\User;
use Auth;
use Illuminate\Database\Eloquent\Builder;

/**
 * Внедрённые через scope в модель User магические методы
 *
 * @mixin User
 *
 * @method static Builder|User ofType(string $type)
 */
trait UserScopes
{
    /**
     * Юзер по типу
     */
    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }
}
