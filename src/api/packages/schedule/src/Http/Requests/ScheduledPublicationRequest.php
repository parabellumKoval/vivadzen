<?php

namespace Backpack\Schedule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduledPublicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'schedulable_type' => 'required|string',
            'schedulable_id' => 'required|integer',
            'publish_at' => 'required|date|after:now',
            'overwrite_created_at' => 'boolean',
            'publish_field' => 'nullable|string',
            'publish_value' => 'nullable|string',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'publish_at' => 'дата публикации',
            'overwrite_created_at' => 'перезаписать дату создания',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'publish_at.after' => 'Дата публикации должна быть в будущем',
        ];
    }
}
