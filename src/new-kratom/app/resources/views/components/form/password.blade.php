@props([
    'name' => 'password',
    'id' => null,
    'value' => '',
    'required' => false,
    'autocomplete' => 'current-password',
    'error' => null,
    'placeholder' => null,
])

@php
    $id = $id ?? 'pass-' . substr(md5($name . uniqid('', true)), 0, 6);
    $hasError = (bool) $error;
@endphp

<div x-data="{ show: false }" class="field__password">
    <input
        :type="show ? 'text' : 'password'"
        id="{{ $id }}"
        name="{{ $name }}"
        value="{{ $value }}"
        autocomplete="{{ $autocomplete }}"
        @if($required) required @endif
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        class="input{{ $hasError ? ' input--error' : '' }}"
        {{ $attributes }}
    />
    <button
        type="button"
        class="field__password-toggle"
        @click="show = !show"
        :aria-label="show ? @js(__('site.account.security.hide_password')) : @js(__('site.account.security.show_password'))"
        :aria-pressed="show.toString()"
        tabindex="-1"
    >
        <template x-if="!show"><x-ui.icon name="eye" :size="18" /></template>
        <template x-if="show"><x-ui.icon name="eye-off" :size="18" /></template>
    </button>
</div>
@if($error)
    <span class="field__error">{{ $error }}</span>
@endif
