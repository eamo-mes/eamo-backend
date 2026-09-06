<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Requests\Equipment\UpdateEquipmentErrorsRequest;
use Modules\Masterdata\Equipment\Services\UpdateEquipmentErrorsService;

final class UpdateEquipmentErrorsAction
{
    use AsAction;

    public function __construct(
        private readonly UpdateEquipmentErrorsService $service
    ) {}

    public function asController(UpdateEquipmentErrorsRequest $request, string $id): JsonResponse
    {
        $equipment = Equipment::findOrFail($id);
        $data = $request->validated();
        $updated = $this->service->update($equipment, $data['equipment_error_ids'], $data['occurred_at'] ?? null);

        return response()->json([
            'status' => 'success',
            'data' => $updated,
        ]);
    }
}
