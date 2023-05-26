<?php

namespace App\Models\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface CommentableInterface
{
    /**
     * Получить все комментарии
     */
    public function comments(): MorphMany;
}
