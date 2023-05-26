<?php

declare(strict_types=1);

namespace App\Http\Requests\Documents;

use App\Models\Document;
use App\Rules\Documents\PackageDocumentRule;
use App\Services\DocumentsService\PackageOfDocuments\PackageDocument;
use App\Services\DocumentsService\PackageOfDocuments\PackageDocumentsService;
use App\Services\PackageAvailableStatus\Exceptions\ForbiddenPackageStatusException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * @property Document $document
 * @property UploadedFile $file
 */
class DocumentUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * @throws \App\Exceptions\NoPackageTypeException
     */
    public function authorize(): bool
    {
        /** @var PackageDocument $documentSlot */
        $documentSlot = PackageDocumentsService::createPackage($this->document->package)
            ->getDocumentBySlot($this->document->slot);
        return (in_array($this->document->package->status_id, $documentSlot->editing, true));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @throws \App\Exceptions\NoPackageTypeException
     */
    public function rules(): array
    {
        return [
            'file' => ['required', new PackageDocumentRule($this->document->package, $this->document->slot)],
        ];
    }

    /**
     * @return void
     * @throws \App\Services\PackageAvailableStatus\Exceptions\ForbiddenPackageStatusException
     */
    protected function failedAuthorization(): void
    {
        throw new ForbiddenPackageStatusException();
    }
}
