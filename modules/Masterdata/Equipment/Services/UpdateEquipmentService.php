<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services;

use Modules\Masterdata\Equipment\Models\Equipment;

final class UpdateEquipmentService
{
    public function __construct(
        private readonly SyncEquipmentStateService $stateService,
        private readonly SyncEquipmentImagesService $imagesService,
        private readonly SyncEquipmentErrorsService $errorsService,
        private readonly SyncEquipmentParametersService $parametersService
    ) {}

    /**
     * Update an existing equipment record and sync associated relations.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $existingImageIds
     * @param  array<int, mixed>  $newFiles
     * @param  array<int, string>|null  $equipmentErrorIds
     * @param  array<int, mixed>|null  $equipmentParameters
     */
    public function update(
        Equipment $equipment,
        array $data,
        ?string $state = null,
        array $existingImageIds = [],
        array $newFiles = [],
        ?array $equipmentErrorIds = null,
        ?array $equipmentParameters = null
    ): Equipment {
        $equipmentData = array_diff_key($data, array_flip(['equipment_parameters', 'state', 'uploaded_images', 'existing_image_ids']));
        $equipment->update($equipmentData);

        if ($state !== null) {
            $this->stateService->set($equipment, $state);
        }

        if (! empty($existingImageIds) || ! empty($newFiles)) {
            $this->imagesService->sync($equipment, $existingImageIds, $newFiles);
        }

        if ($equipmentErrorIds !== null) {
            $this->errorsService->sync($equipment, $equipmentErrorIds);
        }

        if ($equipmentParameters !== null) {
            $this->parametersService->sync($equipment, $equipmentParameters);
        }

        return $equipment->load([
            'equipmentCategory',
            'equipmentErrors',
            'equipmentParameters.unit',
            'equipmentState',
            'equipmentImages',
        ]);
    }
}