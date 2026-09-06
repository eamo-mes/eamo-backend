<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services\Dashboard;

use Modules\Masterdata\Equipment\Models\Equipment;

final class TotalAssetsMetricService
{
    /**
     * Calculate total assets summary widget metric.
     *
     * @return array{title: string, value: int, description: string, icon: string}
     */
    public function execute(): array
    {
        return [
            'title' => 'total_assets',
            'value' => Equipment::count(),
            'description' => 'Total number of assets in the system',
            'icon' => 'DatabaseOutlined',
        ];
    }
}
