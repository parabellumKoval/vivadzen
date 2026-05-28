<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TgProfile;
use App\Services\Telegram\TelegramInitData;
use Backpack\Store\app\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TgProfileController extends Controller
{
    public function __construct(
        protected TelegramInitData $telegramInitData
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $profile = $this->profileFromRequest($request);

        return response()->json([
            'data' => $this->profilePayload($profile),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $profile = $this->profileFromRequest($request);

        $data = $request->validate([
            'first_name' => ['nullable', 'string', 'min:1', 'max:150'],
            'last_name' => ['nullable', 'string', 'min:1', 'max:150'],
            'phone' => ['nullable', 'string', 'min:2', 'max:80'],
            'email' => ['nullable', 'email', 'min:2', 'max:150'],
            'avatar_url' => ['nullable', 'string', 'max:1024'],
            'payment_method' => ['nullable', 'string', 'max:120'],
        ]);

        $profile->fill($this->cleanProfileData($data));
        $profile->save();

        return response()->json([
            'data' => $this->profilePayload($profile),
        ]);
    }

    public function storeAddress(Request $request): JsonResponse
    {
        $profile = $this->profileFromRequest($request);
        $address = $this->addressFromRequest($request);
        $addresses = $this->addresses($profile);

        $addresses = [
            $address,
            ...array_values(array_filter($addresses, fn ($item) => ($item['id'] ?? null) !== $address['id'])),
        ];

        $profile->addresses = array_slice($addresses, 0, 20);
        $profile->save();

        return response()->json([
            'data' => $this->profilePayload($profile),
            'address' => $address,
        ]);
    }

    public function updateAddress(Request $request, string $id): JsonResponse
    {
        $profile = $this->profileFromRequest($request);
        $address = $this->addressFromRequest($request, $id);
        $updated = false;

        $addresses = array_map(function ($item) use ($id, $address, &$updated) {
            if (($item['id'] ?? null) !== $id) {
                return $item;
            }

            $updated = true;
            return $address;
        }, $this->addresses($profile));

        if (!$updated) {
            $addresses = [$address, ...$addresses];
        }

        $profile->addresses = array_slice(array_values($addresses), 0, 20);
        $profile->save();

        return response()->json([
            'data' => $this->profilePayload($profile),
            'address' => $address,
        ]);
    }

    public function destroyAddress(Request $request, string $id): JsonResponse
    {
        $profile = $this->profileFromRequest($request);

        $profile->addresses = array_values(array_filter(
            $this->addresses($profile),
            fn ($item) => ($item['id'] ?? null) !== $id
        ));
        $profile->save();

        return response()->json([
            'data' => $this->profilePayload($profile),
        ]);
    }

    public function orders(Request $request)
    {
        $profile = $this->profileFromRequest($request);
        $telegramUserId = (string) ((int) $profile->telegram_user_id);

        $orders = Order::query()
            ->where('storefront_code', 'telegram')
            ->where(function ($query) use ($telegramUserId) {
                $query->where(function ($ownedQuery) use ($telegramUserId) {
                    $ownedQuery->where('orderable_type', 'telegram')
                        ->where('orderable_id', $telegramUserId);
                });

                $this->orWhereOrderInfoValue($query, '$.telegram_user_id', $telegramUserId);
                $this->orWhereOrderInfoValue($query, '$.telegram_user.id', $telegramUserId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate((int) $request->get('per_page', 12));

        return \Backpack\Store\app\Http\Resources\OrderLargeResource::collection($orders);
    }

    protected function profileFromRequest(Request $request): TgProfile
    {
        $telegram = $this->telegramInitData->fromRequest($request);
        $user = $telegram['user'];

        $profile = TgProfile::firstOrNew([
            'telegram_user_id' => (int) $user['id'],
        ]);

        $profile->fill(array_filter([
            'username' => $this->clean($user['username'] ?? null),
            'first_name' => $profile->first_name ?: $this->clean($user['first_name'] ?? null),
            'last_name' => $profile->last_name ?: $this->clean($user['last_name'] ?? null),
            'avatar_url' => $profile->avatar_url ?: $this->clean($user['photo_url'] ?? null),
            'language_code' => $this->clean($user['language_code'] ?? null),
        ], fn ($value) => $value !== null));

        $profile->addresses = $profile->addresses ?: [];
        $profile->save();

        return $profile;
    }

    protected function addressFromRequest(Request $request, ?string $id = null): array
    {
        $data = $request->validate([
            'id' => ['nullable', 'string', 'max:80'],
            'deliveryMethod' => ['nullable', 'string', 'max:120'],
            'delivery_method' => ['nullable', 'string', 'max:120'],
            'method' => ['nullable', 'string', 'max:120'],
            'settlement' => ['nullable', 'string', 'max:500'],
            'street' => ['nullable', 'string', 'max:255'],
            'house' => ['nullable', 'string', 'max:50'],
            'room' => ['nullable', 'string', 'max:50'],
            'zip' => ['nullable', 'string', 'max:255'],
            'warehouse' => ['nullable', 'string', 'max:500'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'priceCurrency' => ['nullable', 'string', 'size:3'],
            'title' => ['nullable', 'string', 'max:500'],
        ]);

        $method = $data['deliveryMethod']
            ?? $data['delivery_method']
            ?? $data['method']
            ?? null;

        $address = [
            'id' => $id ?: ($data['id'] ?? ('addr-' . (string) Str::uuid())),
            'deliveryMethod' => $this->clean($method),
            'settlement' => $this->clean($data['settlement'] ?? null),
            'street' => $this->clean($data['street'] ?? null),
            'house' => $this->clean($data['house'] ?? null),
            'room' => $this->clean($data['room'] ?? null),
            'zip' => $this->clean($data['zip'] ?? null),
            'warehouse' => $this->clean($data['warehouse'] ?? null),
            'price' => isset($data['price']) ? (float) $data['price'] : null,
            'priceCurrency' => $this->clean($data['priceCurrency'] ?? null),
            'title' => $this->clean($data['title'] ?? null),
            'updatedAt' => now()->toIso8601String(),
        ];

        $address['title'] = $address['title'] ?: $this->addressTitle($address);

        return $address;
    }

    protected function profilePayload(TgProfile $profile): array
    {
        return [
            'telegram_user_id' => $profile->telegram_user_id,
            'username' => $profile->username,
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'phone' => $profile->phone,
            'email' => $profile->email,
            'avatar_url' => $profile->avatar_url,
            'language_code' => $profile->language_code,
            'payment_method' => $profile->payment_method,
            'addresses' => $this->addresses($profile),
        ];
    }

    protected function cleanProfileData(array $data): array
    {
        return array_map(fn ($value) => $this->clean($value), $data);
    }

    protected function addresses(TgProfile $profile): array
    {
        return is_array($profile->addresses) ? array_values($profile->addresses) : [];
    }

    protected function addressTitle(array $address): string
    {
        $streetLine = collect([$address['street'] ?? null, $address['house'] ?? null, $address['room'] ?? null])
            ->filter()
            ->implode(', ');

        $pointLine = $address['warehouse'] ?: ($streetLine ?: ($address['zip'] ?? null));

        return collect([$address['settlement'] ?? null, $pointLine])
            ->filter()
            ->implode(', ') ?: 'Address';
    }

    protected function orWhereOrderInfoValue($query, string $path, string $value): void
    {
        $driver = $query->getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $query->orWhereRaw("CAST(json_extract(info, '{$path}') AS TEXT) = ?", [$value]);
            return;
        }

        $query->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(info, '{$path}')) = ?", [$value]);
    }

    protected function clean($value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return $text === '' ? null : $text;
    }
}
