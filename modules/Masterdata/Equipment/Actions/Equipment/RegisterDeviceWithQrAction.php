<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Requests\Equipment\RegisterDeviceWithQrRequest;
use Modules\Masterdata\Equipment\Services\RegisterDeviceWithQrService;

final class RegisterDeviceWithQrAction
{
    use AsAction;

    public function __construct(
        private readonly RegisterDeviceWithQrService $service
    ) {}

    /**
     * Handle registering an equipment with QR code generation.
     *
     * @param  array<string, mixed>  $equipmentData
     */
    public function handle(array $equipmentData): Equipment
    {
        return $this->service->register($equipmentData);
    }

    public function asController(RegisterDeviceWithQrRequest $request): JsonResponse
    {
        $equipment = $this->handle($request->validated());

        return response()->json([
            'status' => 'success',
            'data' => $equipment->load('equipmentCategory'),
        ], 201);
    }
}
