<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Трейт помощник для работы с интерфейсом MorphableInterface
 *
 * @see \App\Models\Interfaces\MorphableInterface
 */
trait Morphable
{
    /**
     * Получение списка алиасов из классов, к которым может быть применена морфическая связь
     */
    public static function getCanMorphToAliases(): array
    {
        return array_map(fn($class) => app($class)->getMorphClass(), self::getCanMorphTo());
    }

    /**
     * Проверка возможности записи модели в морфическую связь
     *
     * @param Model  $model
     *
     * @return bool
     */
    public static function canMorphModel(Model $model): bool
    {
        return self::canMorphClass($model::class);
    }

    /**
     * Проверка возможности записи модели в морфическую связь
     *
     * @param string  $class
     *
     * @return bool
     */
    public static function canMorphClass(string $class): bool
    {
        return in_array($class, self::getCanMorphTo(), true);
    }
}
