<?php

namespace ParabellumKoval\Dumper\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RestoreDumpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        return [
            'reference' => ['required', 'string'],
        ];
    }
}
