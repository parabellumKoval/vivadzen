<?php

namespace App\Http\Controllers\Api;

use App\Services\AgeVerification\AdultoClient;
use App\Services\AgeVerification\AgeVerificationService;
use App\Services\Telegram\TelegramInitData;
use Backpack\Store\app\Events\ProductAttachedToOrder;
use Backpack\Store\app\Events\PromocodeApplied;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rd\app\Exceptions\DetailedException;

class OrderController extends \Backpack\Store\app\Http\Controllers\Api\OrderController
{
    public function __construct(
        protected AgeVerificationService $ageVerificationService,
        protected AdultoClient $adultoClient,
        protected TelegramInitData $telegramInitData
    ) {
        parent::__construct();
    }

    public function validateOrder(Request $request)
    {
        try {
            $data = $this->validateData($request);
            $data = $this->applyTelegramIdentity($data, $request);
            $products = $this->ensureCartProductsAvailable($data['products'] ?? [], true);
            $this->ensureAgeVerificationIfNeeded($data, $products, $request);

            $user = $this->resolveOrderUser($data);
            $this->verifyBonusRequest($data, $user);

            return true;
        } catch (DetailedException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'options' => $e->getOptions(),
            ], $e->getCode());
        }
    }

    public function create(Request $request)
    {
        try {
            $data = $this->validateData($request);
            $data = $this->applyTelegramIdentity($data, $request);
            $products = $this->ensureCartProductsAvailable($data['products'] ?? [], true);
            $this->ensureAgeVerificationIfNeeded($data, $products, $request);

            $user = $this->resolveOrderUser($data);
            $this->verifyBonusRequest($data, $user);

            [$order] = DB::transaction(function () use ($data, $user) {
                return $this->persistOrder($data, $user);
            });

            ProductAttachedToOrder::dispatch($order);
            if ($order->promocode) {
                PromocodeApplied::dispatch($order);
            }
        } catch (DetailedException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'options' => $e->getOptions(),
            ], $e->getCode());
        }

        return response()->json(new self::$resources['order']['large']($order));
    }

    protected function applyTelegramIdentity(array $data, Request $request): array
    {
        $storefront = strtolower(trim((string) (
            $data['storefront_code']
            ?? $data['storefront']
            ?? $request->header('X-Storefront')
            ?? ''
        )));

        if ($storefront !== 'telegram') {
            return $data;
        }

        $data['storefront'] = 'telegram';
        $data['storefront_code'] = 'telegram';

        if (!$request->header('X-Telegram-Init-Data')) {
            unset($data['telegram_user_id'], $data['telegram_user']);
            return $data;
        }

        try {
            $telegram = $this->telegramInitData->fromRequest($request);
        } catch (\Throwable $exception) {
            throw new DetailedException(
                'Telegram authorization failed.',
                401,
                $exception,
                [
                    'telegram' => ['Telegram authorization failed.'],
                ]
            );
        }

        $user = $telegram['user'];

        $data['telegram_user_id'] = (int) $user['id'];
        $data['telegram_user'] = $user;

        return $data;
    }

    protected function ensureAgeVerificationIfNeeded(array $data, iterable $products, Request $request): void
    {
        $country = strtolower((string) ($request->get('country') ?? \Store::country() ?? ''));

        if (!$this->ageVerificationService->orderRequiresVerification($products, $country)) {
            return;
        }

        if (!$this->adultoClient->isConfigured()) {
            throw new DetailedException(
                'Сервис подтверждения возраста временно недоступен. Попробуйте позже.',
                503,
                null,
                [
                    'age_verification_uid' => ['Сервис подтверждения возраста временно недоступен.'],
                ]
            );
        }

        $uid = trim((string) ($data['age_verification_uid'] ?? ''));

        if ($uid === '') {
            throw new DetailedException(
                'Для оформления товаров 18+ требуется подтверждение возраста.',
                422,
                null,
                [
                    'age_verification_uid' => ['Подтвердите возраст через ADULTO.cz перед оформлением заказа.'],
                ]
            );
        }

        if (!$this->adultoClient->verifyUid($uid)) {
            throw new DetailedException(
                'Подтверждение возраста не прошло проверку.',
                422,
                null,
                [
                    'age_verification_uid' => ['Не удалось подтвердить возраст. Повторите проверку ADULTO.cz.'],
                ]
            );
        }
    }
}
