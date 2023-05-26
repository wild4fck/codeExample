<?php

declare(strict_types=1);

namespace App\Http\Requests\Documents;

use App\Models\Package;
use App\Rules\Documents\PackageDocumentRule;
use App\Services\DocumentsService\PackageOfDocuments\PackageDocumentsService;
use App\Services\PackageAvailableStatus\Exceptions\ForbiddenPackageStatusException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * @property string $slot
 * @property Package $package
 * @property UploadedFile $file
 */
class DocumentStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * @throws \App\Exceptions\NoPackageTypeException
     */
    public function authorize()
    {
        $documentSlot = PackageDocumentsService::createPackage($this->package)->getDocumentBySlot($this->slot);
        return (in_array($this->package->status_id, $documentSlot->can_upload, true));
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
            'slot' => ['required', 'string'],
            'file' => ['required', new PackageDocumentRule($this->package, $this->slot)],
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
