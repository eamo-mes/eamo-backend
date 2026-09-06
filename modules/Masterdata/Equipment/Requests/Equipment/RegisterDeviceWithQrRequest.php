<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\Equipment;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Masterdata\Equipment\Models\Equipment;

final class RegisterDeviceWithQrRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Equipment::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:32', 'unique:eamo_equipment,code'],
            'name' => ['nullable', 'string', 'max:255'],
            'equipment_category_id' => ['nullable', 'uuid', 'exists:eamo_equipment_categories,id'],
            'device_id' => ['nullable', 'uuid', 'unique:eamo_equipment,device_id'],
            'parent_id' => ['nullable', 'uuid', 'exists:eamo_equipment,id'],
            'maintenance_interval_hours' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
