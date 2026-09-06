<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Requests\Equipment\UpdateEquipmentRequest;
use Modules\Masterdata\Equipment\Services\UpdateEquipmentService;

final class UpdateEquipmentAction
{
    use AsAction;

    public function __construct(
        private readonly UpdateEquipmentService $service
    ) {}

    public function asController(UpdateEquipmentRequest $request, string $id): JsonResponse
    {
        $equipment = Equipment::findOrFail($id);

        $updated = $this->service->update(
            $equipment,
            $request->validated(),
            $request->has('state') ? $request->input('state') : null,
            $request->input('existing_image_ids', []),
            $request->file('uploaded_images', []),
            $request->has('equipment_error_ids') ? ($request->input('equipment_error_ids') ?? []) : null,
            $request->has('equipment_parameters') ? ($request->input('equipment_parameters') ?? []) : null
        );

        return response()->json([
            'status' => 'success',
            'data' => $updated,
        ]);
    }
}

