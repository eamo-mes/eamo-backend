<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services;

use App\Models\User;
use Modules\Equipment\Maintenance\Actions\MaintenanceLog\StoreMaintenanceLogAction;
use Modules\Masterdata\Equipment\Models\Equipment;

final class MarkLastMaintenanceService
{
    /**
     * Mark the last maintenance datetime and record a maintenance log.
     *
     * @param  array<string, mixed>  $maintenanceLogData
     * @return array{equipment: Equipment, log: mixed}
     */
    public function mark(Equipment $equipment, array $maintenanceLogData, ?User $user = null): array
    {
        $log = StoreMaintenanceLogAction::run($maintenanceLogData, $user);
        $equipment->refresh();

        return [
            'equipment' => $equipment,
            'log' => $log,
        ];
    }
}
