<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryReportRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $payload = $this->input('payload');

        if (is_string($payload) && trim($payload) !== '') {
            $decoded = json_decode($payload, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge(['payload' => $decoded]);
            }
        }
    }

    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'in:messenger'],
            'order_number' => ['required', 'string', 'max:64'],
            'recipient_fullname' => ['required', 'string', 'max:255'],
            'recipient_actual_fullname' => ['nullable', 'string', 'max:255'],
            'id_card_number' => ['required', 'string', 'max:100'],
            'id_card_type' => ['required', 'string', 'in:op,passport,residence'],
            'handover_place' => ['required', 'string', 'max:255'],
            'handover_datetime' => ['required', 'date'],
            'sender_fullname' => ['required', 'string', 'max:255'],
            'customer_signature' => ['nullable', 'string'],
            'seller_signature' => ['nullable', 'string'],
            'payload' => ['nullable', 'array'],
            'is_test' => ['nullable', 'boolean'],
        ];
    }
}
