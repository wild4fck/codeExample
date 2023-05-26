<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;

/**
 * Хелпер для переработки коллекции для ответа
 *
 * @author AlexBklnv <alexbklnv@yandex.ru>
 */
class CollectionToResponseHelper
{
    /**
     * Подготавливает коллекцию к ответу в коротком виде
     *
     * @param \Illuminate\Http\Resources\Json\AnonymousResourceCollection  $collection
     * @param \Illuminate\Http\Request  $request
     * @param string  $wrap
     *
     * @return array
     */
    public static function buildShortResponse(
        AnonymousResourceCollection $collection,
        Request $request = new Request(),
        string $wrap = 'data'
    ): array {
        $data = $collection->toResponse($request)->getData();

        return [
            $wrap => $data->{$wrap},
            'meta' => Arr::except((array)$data->meta, [
                'from',
                'links',
                'path',
            ]),
        ];
    }
}
