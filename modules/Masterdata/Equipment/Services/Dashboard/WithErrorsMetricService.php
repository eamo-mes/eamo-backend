<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services\Dashboard;

use Modules\Masterdata\Equipment\Models\Equipment;

final class WithErrorsMetricService
{
    /**
     * Calculate equipment with unhandled error logs summary widget metric.
     *
     * @return array{title: string, value: int, description: string, icon: string}
     */
    public function execute(): array
    {
        $withErrors = Equipment::whereHas('errorLogs', function ($q): void {
            $q->whereNull('handled_at');
        })->count();

        return [
            'title' => 'with_errors',
            'value' => $withErrors,
            'description' => 'Number of equipments currently recording unhandled error cases',
            'icon' => 'WarningOutlined',
        ];
    }
}
