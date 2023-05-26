<?php

declare(strict_types=1);

namespace App\Http\Resources\Packages\Logs;

use App\Enums\ModelsNameEnum;
use App\Enums\PackageStatusEnum;
use App\Http\Resources\Users\UserResource;
use App\Models\Document;
use App\Models\PackageLogger;
use App\Services\DocumentsService\PackageOfDocuments\PackageDocumentsService;
use App\Services\Package\LoggerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Lang;

/**
 * @mixin PackageLogger
 * @author AlexBklnv <alexbklnv@yandex.ru>
 */
class PackageLogsResource extends JsonResource
{

    private string $editingObject = 'Пакет';

    private ?string $langField = null;

    /**
     * Transform the resource into an array.
     *
     * @param Request  $request
     *
     * @return array
     * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
     * @throws \App\Exceptions\NoPackageTypeException
     */
    public function toArray(Request $request): array
    {
        $before = $this->before;
        $after = $this->after;

        LoggerService::castInOutByType($before, $after, $this->type);

        $this->determineWhatHasChanged();

        $fieldTransKey = "package_logs.field.{$this->field}";

        return [
            'data' => (new Carbon($this->created_at))->format('d.m.Y H:i:s'),
            'object' => $this->editingObject,
            'field' => $this->langField ?? (Lang::has($fieldTransKey) ? Lang::get($fieldTransKey) : $this->field),
            'before' => $this->determineChangedField($this->before),
            'after' => $this->determineChangedField($this->after),
            'user' => $this->user ? new UserResource($this->user) : 'Автоматически',
            'ip' => $this->ip,
        ];
    }

    /**
     * @throws \App\Exceptions\NoPackageTypeException
     */
    private function determineWhatHasChanged(): void
    {
        if ($this->model && $this->model_id) {
            switch ($this->model) {
                case Document::class:
                    $documentSlot = Document::withTrashed()->findOrFail($this->model_id)->slot;
                    $changedDocument = PackageDocumentsService::createPackage($this->package)
                        ->getDocumentBySlot($documentSlot);
                    $this->editingObject = "Документ \"{$changedDocument->title}\"";
                    break;
                default:
                    $this->editingObject = ModelsNameEnum::getText($this->model);
            }
        }
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    private function determineChangedField($value): mixed
    {
        return match ($this->field) {
            'status' => PackageStatusEnum::getText($value),
            default => $value
        };
    }
}
