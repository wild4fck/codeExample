<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 * @method morphMany(string $class, string $string)
 */
trait Commentable
{
    /**
     * Получить все комментарии
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
