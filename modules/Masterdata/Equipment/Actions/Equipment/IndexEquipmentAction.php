<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Requests\Equipment\IndexEquipmentRequest;
use Modules\Masterdata\Equipment\Services\FetchEquipmentListService;

final class IndexEquipmentAction
{
    use AsAction;

    public function __construct(
        private readonly FetchEquipmentListService $service
    ) {}

    public function asController(IndexEquipmentRequest $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->service->fetch($request->all()),
        ]);
    }
}
