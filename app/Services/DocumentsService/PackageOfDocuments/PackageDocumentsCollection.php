<?php

declare(strict_types=1);

namespace App\Services\DocumentsService\PackageOfDocuments;

use App\Models\User;
use App\Models\Package;
use App\Models\Document;
use App\Enums\UserTypeEnum;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Signature\SignatureResource;
use App\Services\DocumentsService\DocumentsCollectionAbstract;
use RuntimeException;

/**
 * Class PackageDocumentsCollection
 *
 * @package App\Services\PackageOfDocuments\Package
 */
class PackageDocumentsCollection extends DocumentsCollectionAbstract
{

    /**
     * @var Package|null
     */
    public ?Package $package;

    /**
     * @var array<PackageDocument>
     */
    public array $packageTemplate;

    /**
     * PackageDocumentsCollection constructor.
     *
     * @param Package|null  $package
     * @param array  $packageTemplate
     */
    public function __construct(?Package $package, array $packageTemplate)
    {
        $this->package = $package;
        $this->packageTemplate = $packageTemplate;
    }

    /**
     * Получить только системные названия документов из пакета.
     *
     * @return array<string>
     */
    public function keys(): array
    {
        return collect($this->packageTemplate)->pluck('name')->toArray();
    }

    /**
     * Получаем пакет документов с информацией о каждом документе из БД.
     * Загруженность, пути, расширение..
     *
     * @param null|User $user
     *
     * @return PackageDocumentsCollection
     */
    public function fillFromDatabase(?User $user = null): PackageDocumentsCollection
    {
        if (empty($this->package)) {
            return $this;
        }

        $user = $user ?? Auth::user();
        $storage = $this->package->documents;

        $packTemplates = collect($this->packageTemplate)->filter(
            fn(PackageDocument $document) => !$document->hide || !in_array($user->type, $document->hide, true)
        );

        $resultPackage = $packTemplates->map(function (PackageDocument $document) use ($storage, $user) {
                $storageFile = $storage->first(function (Document $item) use ($document) {
                    return ($item->slot === $document->slot);
                });
                return $this->fillTemplatesByStorageData($document, $user, $storageFile);
        });

        $resultPackage = $resultPackage
            ->filter(function (PackageDocument $document) {
                if ($document->uploaded) {
                    return true;
                }
                return in_array($this->package->status_id, $document->can_upload ?? [], true);
            })
            ->values()
            ->toArray();

        return new $this($this->package, $resultPackage);
    }

    /**
     * Получить документ из коллекции по слоту
     *
     * @param string  $slot
     *
     * @return null|\App\Services\DocumentsService\PackageOfDocuments\PackageDocument
     */
    public function getDocumentBySlot(string $slot): ?PackageDocument
    {
        return $this->toCollect()->firstWhere('slot', $slot);
    }

    /**
     * Есть ли нужный документ в коллекции
     *
     * @param string  $documentName
     *
     * @return bool
     */
    public function contains(string $documentName): bool
    {
        return $this->toCollect()->contains(fn($item) => $item->name === $documentName);
    }

    /**
     * Получение шаблона заполненного документом
     *
     * @param Document $document
     * @param null|User $user
     *
     * @return null|\App\Services\DocumentsService\PackageOfDocuments\PackageDocument
     * @throws Exception
     */
    public function getFilledTemplate(Document $document, User $user = null): ?PackageDocument
    {
        if (empty($this->package)) {
            return null;
        }
        $user = $user ?? Auth::user();
        if (!$user) {
            throw new RuntimeException('Действие запрещено для неавторизованного пользователя');
        }
        $podDocument = collect($this->packageTemplate)
            ->filter(fn(PackageDocument $podDocument) => $podDocument->slot === $document->slot
                && (!$podDocument->hide || !in_array($user->type, $podDocument->hide, true)))
            ->first();

        if (!$podDocument) {
            return null;
        }

        return $this->fillTemplatesByStorageData($podDocument, $user, $document);
    }

    /**
     * Заполнение шаблона документа данными загруженного документа
     *
     * @param \App\Services\DocumentsService\PackageOfDocuments\PackageDocument  $document  Шаблон документа
     * @param User $user  Пользователь для проверок
     * @param null|Document $storageDocument  Загруженный документ
     *
     * @return \App\Services\DocumentsService\PackageOfDocuments\PackageDocument
     */
    private function fillTemplatesByStorageData(
        PackageDocument $document,
        User $user,
        ?Document $storageDocument = null
    ): PackageDocument {
        $filledDocument = clone $document;
        $filledDocument->sign_statuses = $document->sign_statuses[$user->type] ?? null;
        unset($filledDocument->hide);
        if ($storageDocument !== null) {
            $filledDocument->id = $storageDocument->id ?? null;
            $filledDocument->name = $storageDocument->name;
            $filledDocument->path = $storageDocument->path;
            $filledDocument->created_at = $storageDocument->created_at;
            $filledDocument->cabinet = $storageDocument->cabinet;
            $filledDocument->uploaded = true;

            $agentSig = $storageDocument->agentSignature();
            $bankSig = $storageDocument->bankSignature();
            $filledDocument->bank_sig_date = $bankSig->created_at ?? null;
            $filledDocument->agent_sig_date = $agentSig->created_at ?? null;
            $filledDocument->signatures = [
                UserTypeEnum::AGENT => $agentSig ? new SignatureResource($agentSig) : null,
                UserTypeEnum::BANK_EMPLOYEE => $bankSig ? new SignatureResource($bankSig) : null,
            ];
        }

        return $filledDocument;
    }
}
