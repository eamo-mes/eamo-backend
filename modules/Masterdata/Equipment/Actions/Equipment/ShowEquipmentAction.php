<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Services\FindEquipmentService;

final class ShowEquipmentAction
{
    use AsAction;

    public function __construct(
        private readonly FindEquipmentService $service
    ) {}

    public function asController(Request $request, string $id): JsonResponse
    {
        $equipment = $this->service->find(
            $id,
            $request->boolean('include_children'),
            $request->boolean('include_parent'),
            $request->boolean('only_trashed'),
            $request->boolean('with_trashed')
        );

        return response()->json([
            'status' => 'success',
            'data' => $equipment,
        ]);
    }
}
