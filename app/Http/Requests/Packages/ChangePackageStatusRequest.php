<?php

namespace App\Http\Requests\Packages;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property \App\Models\Package $package
 * @property int $status
 */
class ChangePackageStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return \string[][]
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'int'],
        ];
    }
}
