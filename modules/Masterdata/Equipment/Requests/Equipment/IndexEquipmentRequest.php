<?php
declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Requests\Equipment;

use Illuminate\Foundation\Http\FormRequest;

final class IndexEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $isAll = $this->boolean('all') || ($this->has('paginate') && ! $this->boolean('paginate'));

        $this->merge([
            'all' => $isAll,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'all' => ['boolean'],
        ];
    }
}
