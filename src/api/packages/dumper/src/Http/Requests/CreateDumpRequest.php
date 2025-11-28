<?php

namespace ParabellumKoval\Dumper\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDumpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:120'],
            'tables' => ['nullable', 'array'],
            'tables.*' => ['string', 'max:191'],
        ];
    }
}
