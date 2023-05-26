<?php

namespace App\Http\Resources\Packages\Logs;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PackageLogsCollection extends ResourceCollection
{
    /**
     * @param $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        //TODO добавить чанк, на фронте сделать обработку постанички
        return ['data' => PackageLogsResource::collection($this->collection)];
    }
}