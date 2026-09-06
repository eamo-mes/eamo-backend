<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Requests\Equipment\MarkLastMaintenanceRequest;
use Modules\Masterdata\Equipment\Services\MarkLastMaintenanceService;

final class MarkLastMaintenanceAction
{
    use AsAction;

    public function __construct(
        private readonly MarkLastMaintenanceService $service
    ) {}

    public function asController(string $id, MarkLastMaintenanceRequest $request): JsonResponse
    {
        $equipment = Equipment::findOrFail($id);
        $result = $this->service->mark($equipment, $request->toMaintenanceLogData($equipment->id), $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Last maintenance datetime updated and log recorded successfully.',
            'last_maintenance' => $result['equipment']->last_maintenance,
            'data' => [
                'equipment_id' => $result['equipment']->id,
                'last_maintenance' => $result['equipment']->last_maintenance,
                'log' => $result['log'],
            ],
        ]);
    }
}
