<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Requests\Equipment\UpdateEquipmentParentRequest;
use Modules\Masterdata\Equipment\Services\UpdateEquipmentParentService;

final class UpdateEquipmentParentAction
{
    use AsAction;

    public function __construct(
        private readonly UpdateEquipmentParentService $service
    ) {}

    public function asController(UpdateEquipmentParentRequest $request, string $id): JsonResponse
    {
        $equipment = Equipment::findOrFail($id);
        $updated = $this->service->updateParent($equipment, $request->validated('parent_id'));

        return response()->json([
            'status' => 'success',
            'data' => $updated,
        ]);
    }
}
