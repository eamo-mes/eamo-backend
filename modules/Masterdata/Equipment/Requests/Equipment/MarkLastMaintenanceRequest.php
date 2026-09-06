<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\Equipment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Masterdata\Equipment\Models\Equipment;

final class MarkLastMaintenanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('markLastMaintenance', Equipment::class) ?? false;
    }

    /**
     * Prepare default values for validation.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if (! $this->has('note') || $this->input('note') === null) {
            $merge['note'] = 'Mark last maintenance';
        }

        if (! $this->has('type') || $this->input('type') === null) {
            $merge['type'] = 'periodic';
        }

        if (! $this->has('result') || $this->input('result') === null) {
            $merge['result'] = 'Completed';
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'datetime' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
            'type' => ['nullable', 'string', 'max:50'],
            'result' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Transform validated request into payload for maintenance log creation.
     *
     * @return array<string, mixed>
     */
    public function toMaintenanceLogData(string $equipmentId): array
    {
        $validated = $this->validated();

        return [
            'equipment_id' => $equipmentId,
            'user_id' => $this->user()?->id,
            'log_date' => $validated['datetime'],
            'note' => $validated['note'],
            'type' => $validated['type'],
            'result' => $validated['result'],
        ];
    }
}
