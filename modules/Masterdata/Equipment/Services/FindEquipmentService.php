<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services;

use Modules\Masterdata\Equipment\Models\Equipment;

final class FindEquipmentService
{
    /**
     * Find equipment by ID with eager-loaded relations and optional soft-deleted records.
     */
    public function find(
        string $id,
        bool $includeChildren = false,
        bool $includeParent = false,
        bool $onlyTrashed = false,
        bool $withTrashed = false
    ): Equipment {
        $relations = [
            'equipmentCategory',
            'equipmentParameters.unit',
            'equipmentErrors',
            'equipmentState',
            'equipmentImages',
        ];

        if ($includeChildren) {
            $relations[] = 'children';
        }

        if ($includeParent) {
            $relations[] = 'parent';
        }

        $query = Equipment::with($relations);

        if ($onlyTrashed) {
            $query->onlyTrashed();
        } elseif ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }
}
