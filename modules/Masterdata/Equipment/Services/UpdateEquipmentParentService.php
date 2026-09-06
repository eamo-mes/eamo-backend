<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services;

use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Masterdata\Equipment\Models\Equipment;

final class UpdateEquipmentParentService
{
    /**
     * Update parent_id of the equipment and prevent circular reference.
     */
    public function updateParent(Equipment $equipment, ?string $parentId): Equipment
    {
        if ($parentId !== null) {
            $ancestor = Equipment::find($parentId);

            while ($ancestor !== null) {
                if ($ancestor->id === $equipment->id) {
                    throw new HttpResponseException(response()->json([
                        'message' => __('equipment.parent_circular_reference'),
                        'errors' => [
                            'parent_id' => [__('equipment.parent_circular_reference_error')],
                        ],
                    ], 422));
                }
                $ancestor = $ancestor->parent;
            }
        }

        $equipment->update([
            'parent_id' => $parentId,
        ]);

        return $equipment->load(['equipmentCategory', 'equipmentState', 'parent']);
    }
}
