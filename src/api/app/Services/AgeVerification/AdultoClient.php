<?php

namespace App\Services\AgeVerification;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdultoClient
{
    public function isConfigured(): bool
    {
        return $this->publicKey() !== '' && $this->privateKey() !== '';
    }

    public function verifyUid(string $uid): bool
    {
        $uid = trim($uid);

        if ($uid === '' || !$this->isConfigured()) {
            return false;
        }

        $cacheKey = sprintf('adulto.verify.%s', sha1($uid));

        return (bool) Cache::remember($cacheKey, now()->addMinutes(30), function () use ($uid) {
            return $this->sendVerifyRequest($uid);
        });
    }

    protected function sendVerifyRequest(string $uid): bool
    {
        $publicKey = $this->publicKey();
        $privateKey = $this->privateKey();
        $verifyUrl = rtrim((string) config('services.adulto.verify_url', 'https://adulto.cz/api/v1/verify'), '/');
        $timeout = (int) config('services.adulto.timeout', 10);
        $signature = hash('sha256', $publicKey . $uid . $privateKey);

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'x-api-key' => $publicKey,
                    'x-signature' => $signature,
                ])
                ->timeout($timeout > 0 ? $timeout : 10)
                ->get(sprintf('%s/%s', $verifyUrl, urlencode($uid)));
        } catch (\Throwable $exception) {
            Log::channel('single')->warning('ADULTO verify request failed.', [
                'message' => $exception->getMessage(),
            ]);

            return false;
        }

        if (!$response->successful()) {
            Log::channel('single')->warning('ADULTO verify response was not successful.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return (bool) data_get($response->json(), 'data.verified', false);
    }

    protected function publicKey(): string
    {
        return trim((string) config('services.adulto.public_key', ''));
    }

    protected function privateKey(): string
    {
        return trim((string) config('services.adulto.private_key', ''));
    }
}
