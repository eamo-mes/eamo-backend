<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Masterdata\Equipment\Models\Equipment;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

final class RegisterDeviceWithQrService
{
    /**
     * Register a new equipment and generate its QR code.
     *
     * @param  array<string, mixed>  $equipmentData
     */
    public function register(array $equipmentData): Equipment
    {
        if (empty($equipmentData['device_id'])) {
            $equipmentData['device_id'] = (string) Str::uuid();
        }

        $uuid = (string) $equipmentData['device_id'];
        $qrPath = $this->generateQrCode($uuid);

        if ($qrPath !== null) {
            $equipmentData['qr_code_path'] = $qrPath;
        }

        return Equipment::create($equipmentData);
    }

    /**
     * Generate SVG QR code for device UUID and save to public storage.
     */
    public function generateQrCode(string $deviceId): ?string
    {
        try {
            $qrImage = QrCode::format('svg')
                ->size(300)
                ->margin(1)
                ->generate($deviceId);

            $qrFileName = "qrcodes/qr_{$deviceId}.svg";
            Storage::disk('public')->put($qrFileName, (string) $qrImage);

            return '/storage/'.$qrFileName;
        } catch (\Throwable $e) {
            logger()->error("Failed to generate QR code locally for equipment with device_id {$deviceId}: ".$e->getMessage());

            return null;
        }
    }
}
