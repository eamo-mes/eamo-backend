<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services\Dashboard;

use Modules\Masterdata\Equipment\Models\Equipment;

final class ActiveInactiveMetricService
{
    /**
     * Calculate active vs inactive equipment summary widget metric.
     *
     * @return array{title: string, value: string, active: int, inactive: int, description: string, icon: string}
     */
    public function execute(): array
    {
        $activeCount = Equipment::where('is_active', true)->count();
        $inactiveCount = Equipment::where('is_active', false)->count();

        return [
            'title' => 'active_inactive',
            'value' => "{$activeCount} / {$inactiveCount}",
            'active' => $activeCount,
            'inactive' => $inactiveCount,
            'description' => 'Ratio of active vs inactive equipment',
            'icon' => 'CheckCircleOutlined',
        ];
    }
}
