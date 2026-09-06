<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services\Dashboard;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Masterdata\Equipment\Models\Equipment;

final class MaintenanceMetricService
{
    /**
     * Calculate maintenance cycle status summary widget metric.
     *
     * @return array{title: string, value: int, overdue: int, upcoming: int, description: string, icon: string}
     */
    public function execute(): array
    {
        $equipments = Equipment::query()
            ->where('is_active', true)
            ->whereNotNull('maintenance_interval_hours')
            ->where('maintenance_interval_hours', '>', 0)
            ->select(['id', 'maintenance_interval_hours', 'last_maintenance'])
            ->get();

        if ($equipments->isEmpty()) {
            return $this->formatResult(0, 0);
        }

        [$overdueCount, $upcomingCount] = $this->calculateCounts($equipments);

        return $this->formatResult($overdueCount, $upcomingCount);
    }

    /**
     * @param  Collection<int, Equipment>  $equipments
     * @return array{0: int, 1: int}
     */
    private function calculateCounts(Collection $equipments): array
    {
        $equipmentIds = $equipments->pluck('id')->all();

        $opTimeSums = DB::table('eamo_operating_times')
            ->whereIn('equipment_id', $equipmentIds)
            ->whereNull('deleted_at')
            ->select('equipment_id', DB::raw('SUM(actual_operating_time) AS total_op'))
            ->groupBy('equipment_id')
            ->get()
            ->keyBy('equipment_id');

        $withCutoff = $equipments->filter(fn (Equipment $e) => ! empty($e->last_maintenance['datetime']));
        $opTimeSumsAfterCutoff = $this->getOperatingTimesAfterCutoff($withCutoff);

        $overdueCount = 0;
        $upcomingCount = 0;

        foreach ($equipments as $equipment) {
            $limit = (float) $equipment->maintenance_interval_hours;
            $hasCutoff = ! empty($equipment->last_maintenance['datetime']);

            $actualOp = $hasCutoff
                ? (float) ($opTimeSumsAfterCutoff->get($equipment->id)->total_op ?? 0)
                : (float) ($opTimeSums->get($equipment->id)->total_op ?? 0);

            $remaining = $limit - $actualOp;

            if ($remaining <= 0) {
                $overdueCount++;
            } elseif ($remaining <= ($limit * 0.1)) {
                $upcomingCount++;
            }
        }

        return [$overdueCount, $upcomingCount];
    }

    /**
     * @param  Collection<int, Equipment>  $equipments
     * @return Collection<string, object>
     */
    private function getOperatingTimesAfterCutoff(Collection $equipments): Collection
    {
        $afterCutoffMap = [];

        foreach ($equipments as $equipment) {
            $cutoff = Carbon::parse($equipment->last_maintenance['datetime']);
            $sum = DB::table('eamo_operating_times')
                ->where('equipment_id', $equipment->id)
                ->whereNull('deleted_at')
                ->where('start_time', '>=', $cutoff)
                ->sum('actual_operating_time');

            $afterCutoffMap[$equipment->id] = (object) ['total_op' => $sum];
        }

        return collect($afterCutoffMap);
    }

    /**
     * @return array{title: string, value: int, overdue: int, upcoming: int, description: string, icon: string}
     */
    private function formatResult(int $overdueCount, int $upcomingCount): array
    {
        return [
            'title' => 'maintenance',
            'value' => $overdueCount + $upcomingCount,
            'overdue' => $overdueCount,
            'upcoming' => $upcomingCount,
            'description' => 'Equipments exceeding or approaching their maintenance cycle limit',
            'icon' => 'ClockCircleOutlined',
        ];
    }
}
