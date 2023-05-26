<?php

declare(strict_types=1);

namespace App\Enums;

use ReflectionClass;

class AbstractEnum
{
    /**
     * @param $id
     *
     * @return bool|int|string
     */
    public static function getNameById($id): bool|int|string
    {
        $constants = collect((new ReflectionClass(static::class))->getConstants())
            ->filter(fn($item) => !is_array($item));
        
        return array_search($id, $constants->toArray(), true);
    }
    
    /**
     * @return array
     */
    public static function toArray(): array
    {
        return (new ReflectionClass(static::class))->getConstants();
    }
    
    /**
     * Именованный константами список, с фильтрацией по общему массиву texts
     *
     * @param string $idKey
     * @param string $titleKey
     * @param string $nameKey
     *
     * @return array
     */
    public static function getNamedList(string $idKey = 'value', string $titleKey = 'title', string $nameKey = 'name'): array
    {
        $constants = collect((new ReflectionClass(static::class))->getConstants())
            ->filter(fn($item) => !is_array($item));
        
        if (isset(static::$texts)) {
            $constants = $constants->filter(fn($textsKey) => isset(static::$texts[$textsKey]));
        }
        
        return $constants->mapWithKeys(
            fn($value, $name) => [
            $name => [
                $idKey => $value,
                $nameKey => $name,
                $titleKey => self::getText($value),
            ],
            ]
        )->toArray();
    }
    
    /**
     * Возвращает текст по ключу
     *
     * @param $key
     *
     * @return mixed
     */
    public static function getText($key): mixed
    {
        return static::$texts[$key] ?? $key;
    }
    
    /**
     * Возвращает массив ключей из $texts.
     *
     * @return array
     */
    public static function getTextsKeys(): array
    {
        if (static::$texts) {
            return array_keys(static::$texts);
        }
        
        return [];
    }
    
    /**
     * @param $searchValue
     *
     * @return bool|int|string
     */
    public static function getKeyByValue($searchValue): bool|int|string
    {
        $values = (new ReflectionClass(static::class))->getConstants();
        return array_search($searchValue, $values, true);
    }
}
