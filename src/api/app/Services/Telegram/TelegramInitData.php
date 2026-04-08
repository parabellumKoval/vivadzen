<?php

namespace App\Services\Telegram;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class TelegramInitData
{
    public function fromRequest(Request $request): array
    {
        $initData = trim((string) $request->header('X-Telegram-Init-Data', ''));

        if ($initData === '') {
            throw new UnauthorizedHttpException('', 'Telegram initData is missing.');
        }

        return $this->validate($initData);
    }

    public function validate(string $initData): array
    {
        $botToken = (string) config('services.telegram.bot_token', '');

        if ($botToken === '') {
            throw new HttpException(503, 'Telegram bot token is not configured.');
        }

        parse_str($initData, $data);

        if (empty($data['hash']) || !is_string($data['hash'])) {
            throw new UnauthorizedHttpException('', 'Telegram initData hash is missing.');
        }

        $hash = $data['hash'];
        unset($data['hash']);

        ksort($data);

        $dataCheckString = collect($data)
            ->map(function ($value, $key) {
                $preparedValue = is_array($value) ? json_encode($value) : $value;

                return "{$key}={$preparedValue}";
            })
            ->implode("\n");

        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        if (!hash_equals($calculatedHash, $hash)) {
            throw new UnauthorizedHttpException('', 'Telegram initData hash is invalid.');
        }

        $maxAge = (int) config('services.telegram.init_data_max_age', 604800);
        $authDate = isset($data['auth_date']) ? (int) $data['auth_date'] : 0;

        if ($maxAge > 0 && $authDate > 0 && $authDate < now()->subSeconds($maxAge)->timestamp) {
            throw new UnauthorizedHttpException('', 'Telegram initData is expired.');
        }

        $user = [];

        if (!empty($data['user']) && is_string($data['user'])) {
            $decoded = json_decode($data['user'], true);

            if (is_array($decoded)) {
                $user = $decoded;
            }
        }

        if (empty($user['id'])) {
            throw new UnauthorizedHttpException('', 'Telegram user is missing.');
        }

        return [
            'raw' => $data,
            'user' => $user,
        ];
    }
}
