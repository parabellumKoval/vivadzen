<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => PaymentMethod::orderBy('position')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());
        $method = PaymentMethod::create($data);
        return response()->json(['data' => $method], 201);
    }

    public function show(PaymentMethod $paymentMethod): JsonResponse
    {
        return response()->json(['data' => $paymentMethod]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $data = $request->validate($this->rules($paymentMethod->id));
        $paymentMethod->update($data);
        return response()->json(['data' => $paymentMethod->fresh()]);
    }

    public function destroy(PaymentMethod $paymentMethod): JsonResponse
    {
        $paymentMethod->delete();
        return response()->json(['ok' => true]);
    }

    /**
     * Массовая смена порядка: { order: [id, id, …] }.
     */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer', 'min:1'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['order'] as $position => $id) {
                PaymentMethod::where('id', $id)->update(['position' => $position + 1]);
            }
        });

        return response()->json([
            'data' => PaymentMethod::orderBy('position')->orderBy('id')->get(),
        ]);
    }

    /** @return array<string, mixed> */
    private function rules(?int $id = null): array
    {
        return [
            'code' => ['required', 'string', 'max:64', Rule::unique('payment_methods', 'code')->ignore($id)],
            'type' => ['required', Rule::in(['cod', 'qr', 'bank', 'online'])],
            'name' => ['required', 'array'],
            'name.cs' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'array'],
            'fee' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0'],
            'delivery_method_codes' => ['nullable', 'array'],
            'delivery_method_codes.*' => ['string', 'max:64'],
            'settings' => ['nullable', 'array'],
        ];
    }
}
