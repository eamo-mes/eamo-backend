<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Requests\Equipment\StoreEquipmentRequest;
use Modules\Masterdata\Equipment\Services\SyncEquipmentErrorsService;
use Modules\Masterdata\Equipment\Services\SyncEquipmentImagesService;
use Modules\Masterdata\Equipment\Services\SyncEquipmentParametersService;
use Modules\Masterdata\Equipment\Services\SyncEquipmentStateService;

final class StoreEquipmentAction
{
    use AsAction;

    public function __construct(
        private readonly SyncEquipmentStateService $stateService,
        private readonly SyncEquipmentImagesService $imagesService,
        private readonly SyncEquipmentErrorsService $errorsService,
        private readonly SyncEquipmentParametersService $parametersService
    ) {}

    /**
     * Store a new equipment record with associated state, images, errors, and parameters.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, mixed>  $uploadedImages
     * @param  array<int, string>  $equipmentErrorIds
     * @param  array<int, mixed>  $equipmentParameters
     */
    public function handle(
        array $data,
        ?string $state = null,
        array $uploadedImages = [],
        array $equipmentErrorIds = [],
        array $equipmentParameters = []
    ): Equipment {
        $equipmentData = array_diff_key($data, array_flip(['equipment_parameters', 'state', 'uploaded_images']));

        $equipmentData['last_maintenance'] = [
            'datetime' => now()->toIso8601String(),
        ];

        $equipment = RegisterDeviceWithQrAction::run($equipmentData);

        if (! empty($state)) {
            $this->stateService->create($equipment, $state);
        }

        if (! empty($uploadedImages)) {
            $this->imagesService->uploadImages($equipment, $uploadedImages);
        }

        if (! empty($equipmentErrorIds)) {
            $this->errorsService->sync($equipment, $equipmentErrorIds);
        }

        if (! empty($equipmentParameters)) {
            $this->parametersService->create($equipment, $equipmentParameters);
        }

        return $equipment->load([
            'equipmentCategory',
            'equipmentErrors',
            'equipmentParameters.unit',
            'equipmentState',
            'equipmentImages',
        ]);
    }

    public function asController(StoreEquipmentRequest $request): JsonResponse
    {
        $equipment = $this->handle(
            $request->validated(),
            $request->input('state'),
            $request->file('uploaded_images', []),
            $request->input('equipment_error_ids', []),
            $request->input('equipment_parameters', [])
        );

        return response()->json([
            'status' => 'success',
            'data' => $equipment,
        ], 201);
    }
}

