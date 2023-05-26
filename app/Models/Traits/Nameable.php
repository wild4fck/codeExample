<?php

declare(strict_types=1);

namespace App\Models\Traits;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
trait Nameable
{
    /**
     * Получение полной строки имени
     *
     * @return string
     */
    public function getNameAttribute(): string
    {
        return trim(sprintf('%s %s %s', $this->lastname, $this->firstname, $this->patronymic));
    }
}
