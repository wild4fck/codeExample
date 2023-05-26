<?php

declare(strict_types=1);

namespace App\Http\Requests\Packages;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class PackagesIndexRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'pagination.page' => ['nullable', 'int'],
            'pagination.perPage' => ['nullable', 'int'],
            'filters.packageType' => ['nullable', 'array'],
            'filters.status' => ['required', 'integer', 'min:0', 'max:100'],
            'filters.name' => ['nullable', 'string'],
            'filters.agentName' => ['nullable', 'string'],
            'filters.period' => ['nullable', 'date'],
            'sort' => ['nullable', 'array'],
            'sort.order_by' => ['nullable', 'string'],
            'sort.direction' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
