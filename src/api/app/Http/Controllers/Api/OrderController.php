<?php

namespace App\Http\Controllers\Api;

use App\Services\AgeVerification\AdultoClient;
use App\Services\AgeVerification\AgeVerificationService;
use Backpack\Store\app\Events\ProductAttachedToOrder;
use Backpack\Store\app\Events\PromocodeApplied;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rd\app\Exceptions\DetailedException;

class OrderController extends \Backpack\Store\app\Http\Controllers\Api\OrderController
{
    public function __construct(
        protected AgeVerificationService $ageVerificationService,
        protected AdultoClient $adultoClient
    ) {
        parent::__construct();
    }

    public function validateOrder(Request $request)
    {
        try {
            $data = $this->validateData($request);
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
