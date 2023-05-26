<?php

declare(strict_types=1);

namespace App\Services\DocumentsService;

use Illuminate\Support\Collection;

/**
 * Class DocumentsCollectionAbstract
 *
 * @package App\Services\PackageOfDocuments\Package
 */
abstract class DocumentsCollectionAbstract
{
    /**
     * @var array
     */
    public array $packageTemplate;
    
    /**
     * Получить полный пакет.
     *
     * @return array
     */
    public function get(): array
    {
        return $this->packageTemplate;
    }
    
    /**
     * Конвертировать в массив.
     *
     * @return array
     * @throws \JsonException
     */
    public function toArray(): array
    {
        return json_decode(json_encode($this->packageTemplate, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }
    
    /**
     * Конвертировать в JSON.
     *
     * @return false|string
     * @throws \JsonException
     */
    public function toJson(): bool|string
    {
        return json_encode($this->packageTemplate, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Получить Laravel Коллекцию.
     *
     * @return \Illuminate\Support\Collection
     */
    public function toCollect(): Collection
    {
        return collect($this->packageTemplate);
    }
    
    /**
     * @return $this
     */
    abstract public function fillFromDatabase(): self;
}
