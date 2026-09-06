<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Models\OperatingTime;
use Modules\Masterdata\Equipment\Models\Equipment;

final class GetMaintenanceStatusChartAction
{
    use AsAction;

    /**
     * Calculate remaining operating hours until next maintenance for active equipment.
     *
     * @return array<int, array{name: string, remaining: float}>
     */
    public function handle(): array
    {
        $equipments = Equipment::query()
            ->where('is_active', true)
            ->whereNotNull('maintenance_interval_hours')
            ->where('maintenance_interval_hours', '>', 0)
            ->get(['id', 'code', 'maintenance_interval_hours', 'last_maintenance']);

        return $equipments->map(function (Equipment $equipment): array {
            $lastMaintenanceDate = $equipment->last_maintenance['datetime'] ?? null;

            $actualOperatingTime = OperatingTime::query()
                ->where('equipment_id', $equipment->id)
                ->when(
                    $lastMaintenanceDate,
                    fn ($query) => $query->where('start_time', '>=', Carbon::parse($lastMaintenanceDate))
                )
                ->sum('actual_operating_time');

            $remaining = (float) $equipment->maintenance_interval_hours - (float) $actualOperatingTime;

            return [
                'name' => $equipment->code,
                'remaining' => round($remaining, 2),
            ];
        })
            ->sortBy('remaining')
            ->values()
            ->all();
    }

    public function asController(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->handle(),
        ]);
    }
}
