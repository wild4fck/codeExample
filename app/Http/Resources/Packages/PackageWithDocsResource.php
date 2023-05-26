<?php

declare(strict_types=1);

namespace App\Http\Resources\Packages;

use App\Services\Comment\Catalog\DocumentCommentator;
use App\Services\DocumentsService\PackageOfDocuments\PackageDocumentsService;
use App\Services\PackageAvailableStatus\PackageAvailableStatusService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Package
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class PackageWithDocsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request  $request
     *
     * @return array
     * @throws \App\Exceptions\NoPackageTypeException
     */
    public function toArray(Request $request): array
    {
        $documents = PackageDocumentsService::createPackage($this->resource)
            ->fillFromDatabase()
            ->toCollect();

        return [
            'package' => [
                ...(new PackageResource($this->resource))->toArray(null),
                'documents' => $documents->toArray(),
            ],
            'unreadCommentsCount' => DocumentCommentator::getUserCommentsCountList($documents),
            'availableStatuses' => (object)PackageAvailableStatusService::getAvailableStatuses($this->resource, Auth::user())
        ];
    }
}
