<?php

declare(strict_types=1);

namespace App\Repository\Interfaces;

use Illuminate\Database\Eloquent\Model;

/**
 * Interface BaseRepositoryInterface
 *
 * @package App\Repository\Contracts
 */
interface BaseRepositoryInterface
{
    /**
     * получение всех данных с условиями
     *
     * @param array  $data
     *
     * @return mixed
     */
    public function all(array $data = null);
    
    /**
     * получение по ID
     *
     * @param int  $id
     *
     * @return mixed
     */
    public function get(int $id);
    
    /**
     * запись
     *
     * @param array  $data
     *
     * @return mixed
     */
    public function store(array $data);
    
    /**
     * одновление по  ID
     *
     * @param int|Model  $model
     * @param array  $data
     *
     * @return mixed
     */
    public function update(Model|int $model, array $data);
    
    /**
     * удаление
     *
     * @param int  $id
     *
     * @return mixed
     */
    public function delete(int $id);
}