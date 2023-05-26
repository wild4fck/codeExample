<?php

namespace App\Repository;

use App\Enums\LoggerDataTypeEnum;
use App\Models\Document;
use App\Models\PackageLogger;
use App\Services\DocumentsService\PackageOfDocuments\PackageDocument;
use App\Services\DocumentsService\PackageOfDocuments\PackageDocumentsService;
use App\Services\PackageAvailableStatus\Exceptions\ForbiddenPackageStatusException;
use Illuminate\Database\Eloquent\Model;

class DocumentRepository extends BaseAbstract
{
    public function __construct(Document $model)
    {
        parent::__construct($model);
    }

    /**
     * @param $model
     * @param array  $data
     *
     * @return mixed
     */
    public function update($model, array $data): mixed
    {
        /** @var \App\Models\Document $model */
        $before = array_intersect_key($model->toArray(), $data);
        $diffKeys = array_keys(array_diff($before, $data));

        $document = parent::update($model, $data);

        foreach ($diffKeys as $key) {
            PackageLogger::add(
                $model->package,
                $key,
                $before[$key] ?? '',
                $data[$key] ?? '',
                LoggerDataTypeEnum::DATA_TYPE_STRING,
                $model
            );
        }

        return $document;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model|int  $model
     *
     * @return null|bool
     * @throws \App\Exceptions\NoPackageTypeException
     * @throws \App\Services\PackageAvailableStatus\Exceptions\ForbiddenPackageStatusException
     */
    public function delete(Model|int $model): ?bool
    {
        /** * @var \App\Models\Document $model */
        if (is_numeric($model)) {
            $model = $this->model->find($model);
        }

        /** @var PackageDocument $documentSlot */
        $documentSlot = PackageDocumentsService::createPackage($model->package)->getDocumentBySlot($model->slot);

        if (!in_array($model->package->status_id, $documentSlot->editing, true)) {
            throw new ForbiddenPackageStatusException();
        }

        PackageLogger::add(
            package: $model->package,
            before: 'Загружен',
            after: 'Удалён',
            model: $model
        );
        return $model->delete();
    }
}
