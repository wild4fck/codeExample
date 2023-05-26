<?php

namespace App\Services\DocumentsService;

interface PackageInterface
{
    /**
     * Сформировать пакет документов
     *
     * @return mixed
     */
    public function make(): array;
    
    /**
     * Сформировать полный пакет документов без условий
     *
     * @return mixed
     */
    public function makeWithoutConditions(): array;
    
    /**
     * Получить коллекцию документов
     *
     * @param bool  $conditions
     *
     * @return DocumentsCollectionAbstract
     */
    public function getCollection(bool $conditions = true): DocumentsCollectionAbstract;
}
