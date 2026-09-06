<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Services\Dashboard\ActiveInactiveMetricService;
use Modules\Masterdata\Equipment\Services\Dashboard\MaintenanceMetricService;
use Modules\Masterdata\Equipment\Services\Dashboard\TotalAssetsMetricService;
use Modules\Masterdata\Equipment\Services\Dashboard\WithErrorsMetricService;

final class GetDashboardSummaryAction
{
    use AsAction;

    /**
     * Get statistics summary for dashboard widgets.
     *
     * @return array<string, array<string, mixed>>
     */
    public function __construct(
        private readonly TotalAssetsMetricService $totalAssets,
        private readonly ActiveInactiveMetricService $activeInactive,
        private readonly WithErrorsMetricService $withErrors,
        private readonly MaintenanceMetricService $maintenance
    ){}

    public function handle(): array
    {
        return [
            'total_assets' => $this->totalAssets->execute(),
            'active_inactive' => $this->activeInactive->execute(),
            'with_errors' => $this->withErrors->execute(),
            'maintenance' => $this->maintenance->execute(),
        ];
    }

    public function asController(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->handle(),
        ]);
    }
}
