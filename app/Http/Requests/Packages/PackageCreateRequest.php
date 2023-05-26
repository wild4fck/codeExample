<?php

declare(strict_types=1);

namespace App\Http\Requests\Packages;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $type Тип пакета
 * @property integer $agent_id ID агента из таблицы Agents
 * @property mixed $package_date Месяц пакета
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class PackageCreateRequest extends FormRequest
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
            'type' => ['required', 'string'],
            'agent_id' => ['required', 'integer'],
            'package_date' => ['required', 'date'],
        ];
    }
}
