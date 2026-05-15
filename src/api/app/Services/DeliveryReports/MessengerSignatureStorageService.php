<?php

namespace App\Services\DeliveryReports;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class MessengerSignatureStorageService
{
    protected const DISK = 'uploads';

    protected const FOLDER = 'delivery-reports/messenger/signatures';

    public function storeDataUri(string $dataUri, string $orderNumber, string $type): string
    {
        if (! preg_match('/^data:image\/png;base64,(?<data>.+)$/', $dataUri, $matches)) {
            throw new RuntimeException('Messenger signature must be a PNG data URI.');
        }

        $binary = base64_decode($matches['data'], true);

        if ($binary === false || $binary === '') {
            throw new RuntimeException('Messenger signature contains invalid base64 PNG data.');
        }

        $safeOrderNumber = Str::slug($orderNumber);

        if ($safeOrderNumber === '') {
            $safeOrderNumber = 'order';
        }

        $safeType = in_array($type, ['customer', 'seller'], true) ? $type : 'signature';

        $path = sprintf(
            '%s/%s/%s-%s-%s.png',
            self::FOLDER,
            now()->format('Y/m'),
            $safeOrderNumber,
            $safeType,
            Str::random(12)
        );

        if (! Storage::disk(self::DISK)->put($path, $binary)) {
            throw new RuntimeException('Failed to store Messenger signature file.');
        }

        return '/uploads/'.$path;
    }
}
