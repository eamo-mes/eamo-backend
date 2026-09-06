<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Masterdata\Equipment\Models\Equipment;

final class UpdateEquipmentErrorsService
{
    /**
     * Update equipment error log definitions.
     *
     * @param  array<int, string>  $errorIds
     */
    public function update(Equipment $equipment, array $errorIds, ?string $occurredAt = null): Equipment
    {
        $errorIds = array_values(array_unique(array_filter($errorIds)));

        DB::transaction(function () use ($equipment, $errorIds, $occurredAt): void {
            $now = now();

            // Soft delete definition records no longer in the list
            DB::table('eamo_equipment_error_logs')
                ->where('equipment_id', $equipment->id)
                ->whereNull('occurred_at')
                ->whereNotIn('equipment_error_id', $errorIds)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now]);

            // Restore soft-deleted or insert new definition records for requested error IDs
            foreach ($errorIds as $errorId) {
                $definition = DB::table('eamo_equipment_error_logs')
                    ->where('equipment_id', $equipment->id)
                    ->where('equipment_error_id', $errorId)
                    ->first();

                if ($definition) {
                    DB::table('eamo_equipment_error_logs')
                        ->where('id', $definition->id)
                        ->update([
                            'occurred_at' => $definition->occurred_at ?? $occurredAt,
                            'deleted_at' => null,
                            'updated_at' => $now,
                        ]);
                } else {
                    DB::table('eamo_equipment_error_logs')->insert([
                        'id' => (string) Str::uuid(),
                        'equipment_id' => $equipment->id,
                        'equipment_error_id' => $errorId,
                        'occurred_at' => $occurredAt,
                        'restarted_at' => null,
                        'handled_at' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]);
                }
            }
        });

        return $equipment->fresh()->load(['equipmentCategory', 'equipmentErrors', 'equipmentState', 'equipmentImages']);
    }
}
