<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Requests\Equipment\DecodeQrRequest;
use Modules\Masterdata\Equipment\Services\DecodeQrEquipmentService;
use Modules\Masterdata\Equipment\Models\Equipment;

final class DecodeQrAndGetEquipmentAction
{
    use AsAction;

    public function __construct(
        private readonly DecodeQrEquipmentService $service
    ) {}

    public function asController(DecodeQrRequest $request): JsonResponse
    {
        $file = $request->file('qr_image');
        $equipment = $this->service->decodeAndFind($file->getPathname());

        return response()->json([
            'status' => 'success',
            'message' => __('equipment.qr_decoded_success'),
            'data' => $equipment->load([
                'equipmentCategory',
                'equipmentErrors',
                'equipmentParameters.unit',
                'equipmentState',
                'equipmentImages',
            ]),
        ]);
    }
}
