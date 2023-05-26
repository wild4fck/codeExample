<?php

namespace App\Models\Interfaces;

interface MorphableInterface
{
    /**
     * Получение списка классов, которые могут быть записаны в полиморфную связь
     *
     * @return array
     */
    public static function getCanMorphTo(): array;
}
