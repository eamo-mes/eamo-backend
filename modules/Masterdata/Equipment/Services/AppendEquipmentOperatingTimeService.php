<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Masterdata\Equipment\Models\Equipment;

final class AppendEquipmentOperatingTimeService
{
    /**
     * Append actual_operating_time to each equipment in the collection.
     *
     * @param  iterable<Equipment>|Collection<int, Equipment>  $equipments
     */
    public function append(iterable $equipments): void
    {
        $collection = $equipments instanceof Collection ? $equipments : collect($equipments);

        if ($collection->isEmpty()) {
            return;
        }

        $equipmentIds = $collection->pluck('id')->all();

        // 1. Fetch total operating times sum from beginning of time for these equipments
        $opTimeSums = DB::table('eamo_operating_times')
            ->whereIn('equipment_id', $equipmentIds)
            ->whereNull('deleted_at')
            ->select('equipment_id', DB::raw('SUM(actual_operating_time) AS total_op'))
            ->groupBy('equipment_id')
            ->get()
            ->keyBy('equipment_id');

        // 2. Fetch operating times sum after cutoff for equipments with last_maintenance
        $withCutoffIds = $collection
            ->filter(fn ($e) => ! empty($e->last_maintenance['datetime']))
            ->all();

        $opTimeSumsAfterCutoff = collect();
        if (! empty($withCutoffIds)) {
            $afterCutoffMap = [];
            foreach ($withCutoffIds as $equipment) {
                $cutoff = Carbon::parse($equipment->last_maintenance['datetime']);
                $sum = DB::table('eamo_operating_times')
                    ->where('equipment_id', $equipment->id)
                    ->whereNull('deleted_at')
                    ->where('start_time', '>=', $cutoff)
                    ->sum('actual_operating_time');
                $afterCutoffMap[$equipment->id] = $sum;
            }
            $opTimeSumsAfterCutoff = collect($afterCutoffMap);
        }

        // 3. Append actual_operating_time to each equipment object
        foreach ($collection as $equipment) {
            $hasCutoff = ! empty($equipment->last_maintenance['datetime']);
            if ($hasCutoff) {
                $actualOp = (float) ($opTimeSumsAfterCutoff->get($equipment->id) ?? 0);
            } else {
                $actualOp = (float) ($opTimeSums->get($equipment->id)->total_op ?? 0);
            }
            $equipment->actual_operating_time = round($actualOp, 2);
        }
    }
}
