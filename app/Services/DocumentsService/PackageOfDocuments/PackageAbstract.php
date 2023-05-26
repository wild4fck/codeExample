<?php

declare(strict_types=1);

namespace App\Services\DocumentsService\PackageOfDocuments;

use App\Models\Document;
use App\Models\Package;
use App\Models\User;
use App\Services\DocumentsService\PackageInterface;
use Auth;

/**
 * Абстрактный класс формирования пакета документов
 * Класс представляет пакет документов и содержит методы для получения и установки атрибутов пакета.
 */
abstract class PackageAbstract implements PackageInterface
{
    /**
     * @var null|\App\Models\Package
     */
    protected ?Package $package;

    /**
     * @var array
     */
    protected array $attributes;

    /**
     * @var \App\Models\User
     */
    protected User $user;

    /**
     * AbstractPackage constructor.
     *
     * @param null|\App\Models\Package  $package
     * @param array  $attributes
     */
    public function __construct(Package $package = null, array $attributes = [])
    {
        $this->package = $package;
        $this->attributes = $attributes;
        $this->user = $this->attributes['user'] ?? Auth::user();
    }

    /**
     * @param string  $slot
     *
     * @return PackageDocument
     */
    public function getDocumentBySlot(string $slot): PackageDocument
    {
        return $this->getCollection(false)->toCollect()->firstWhere('slot', $slot);
    }

    /** * @inheritDoc */
    public function getCollection(bool $conditions = true): PackageDocumentsCollection
    {
        $packageTemplate = $conditions ? $this->make() : $this->makeWithoutConditions();

        collect($packageTemplate)->map(function (PackageDocument $document) {
            $document->editing = $this->processPermissionsToBlock($document->editing);
            $document->can_upload = $this->processPermissionsToBlock($document->can_upload);
        });

        return new PackageDocumentsCollection($this->package, $packageTemplate);
    }

    /**
     * @param \App\Models\Document  $document
     *
     * @return \App\Services\DocumentsService\PackageOfDocuments\PackageDocument
     * @throws \Exception
     */
    public function getFilledTemplate(Document $document): PackageDocument
    {
        return $this->getCollection(false)->getFilledTemplate($document);
    }

    /**
     * @return array
     */
    public function getVisibleSlotsByUserType(): array
    {
        return $this->getCollection(false)
            ->toCollect()
            ->filter(fn($document) => !$document->hide || !in_array(Auth::user()->type, $document->hide, true))
            ->pluck('slot')->toArray();
    }

    /**
     * Получение списка статусов доступных пользователю на совершение действия
     *
     * @param array  $editingMap
     *
     * @return array
     */
    private function processPermissionsToBlock(array $editingMap): array
    {
        $map = $editingMap[$this->user->type] ?? null;
        if (!$map) {
            return [];
        }

        if (User::isUserAgent($this->user)) {
            return $map;
        }

        return collect($map)
            ->filter(fn($permissions) => empty($permissions) || $this->user->canAny($permissions))
            ->map(fn($permissions, $status) => $status)
            ->values()
            ->toArray();
    }
}
