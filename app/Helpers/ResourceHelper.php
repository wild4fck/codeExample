<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Вспомогательные функции для форматирования выдаваемых в ресурсах данных
 */
class ResourceHelper
{
    /**
     * Именованный числовыми идентификаторами список.
     * Префикс id_ ставится для корректного формирования js объекта (чтоб был именно объектом, а не массивом)
     *
     *
     * @param $array
     *
     * @return array
     */
    public static function getNumerableList($array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result["id_$key"] = [
                'value' => $key,
                'title' => $value,
            ];
        }
    
        return $result;
    }
}