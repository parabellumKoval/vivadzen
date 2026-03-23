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
        $privateKey = $this->privateKey();
        $verifyUrl = rtrim((string) config('services.adulto.verify_url', 'https://api.result.adulto.cz'), '/');
        $timeout = (int) config('services.adulto.timeout', 10);

        try {
            $response = Http::acceptJson()
                ->timeout($timeout > 0 ? $timeout : 10)
                ->get($verifyUrl, [
                    'secret' => $privateKey,
                    'response' => $uid,
                ]);
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

        $payload = $response->json();
        $verifiedUid = trim((string) data_get($payload, 'adultocz-verify-uid', ''));
        $isAdult = data_get($payload, 'adultocz-verify-adult');

        if ($verifiedUid === '' || !hash_equals($uid, $verifiedUid)) {
            Log::channel('single')->warning('ADULTO verify response uid mismatch.', [
                'expected_uid' => $uid,
                'actual_uid' => $verifiedUid,
                'payload' => $payload,
            ]);

            return false;
        }

        return filter_var($isAdult, FILTER_VALIDATE_BOOLEAN);
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
