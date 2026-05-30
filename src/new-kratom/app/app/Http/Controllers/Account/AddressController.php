<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AddressController extends Controller
{
    /** @return array<string, mixed> */
    private function rules(): array
    {
        return [
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')],
            'street' => ['required', 'string', 'max:128'],
            'phone' => ['nullable', 'string', 'max:64'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());
        $user = $request->user();

        $isDefault = (bool) ($data['is_default'] ?? false) || $user->addresses()->count() === 0;

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            'city_id' => $data['city_id'],
            'street' => $data['street'],
            'phone' => $data['phone'] ?? null,
            'is_default' => $isDefault,
        ]);

        return response()->json(['ok' => true, 'data' => $this->present($address->fresh())], 201);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        $this->authorizeOwner($request, $address);

        $data = $request->validate($this->rules());

        if ((bool) ($data['is_default'] ?? false)) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update([
            'city_id' => $data['city_id'],
            'street' => $data['street'],
            'phone' => $data['phone'] ?? null,
            'is_default' => (bool) ($data['is_default'] ?? $address->is_default),
        ]);

        return response()->json(['ok' => true, 'data' => $this->present($address->fresh())]);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        $this->authorizeOwner($request, $address);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $next = $request->user()->addresses()->first();
            $next?->update(['is_default' => true]);
        }

        return response()->json(['ok' => true]);
    }

    public function setDefault(Request $request, Address $address): JsonResponse
    {
        $this->authorizeOwner($request, $address);

        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json(['ok' => true]);
    }

    private function authorizeOwner(Request $request, Address $address): void
    {
        abort_unless($address->user_id === $request->user()->id, 403);
    }

    /** @return array<string, mixed> */
    public static function present(Address $address): array
    {
        $address->loadMissing('city');
        return [
            'id' => $address->id,
            'city_id' => $address->city_id,
            'city' => $address->city ? [
                'id' => $address->city->id,
                'name' => $address->city->name,
                'district_name' => $address->city->district_name,
                'region_name' => $address->city->region_name,
                'full_label' => $address->city->full_label,
            ] : null,
            'street' => $address->street,
            'phone' => $address->phone,
            'is_default' => (bool) $address->is_default,
        ];
    }
}
