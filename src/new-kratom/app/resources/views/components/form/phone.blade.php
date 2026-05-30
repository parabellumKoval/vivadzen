@props([
    'name' => 'phone',
    'id' => null,
    'value' => '',
    'region' => 'cz',
    'required' => false,
    'autocomplete' => 'tel',
    'error' => null,
])

@php
    $id = $id ?? 'phone-' . substr(md5($name . uniqid('', true)), 0, 6);
    $hasError = (bool) $error;
@endphp

<div
    x-data="phoneMask({ initial: @js((string) $value), region: @js($region) })"
    x-modelable="display"
    class="field__phone"
    {{ $attributes }}
>
    <input
        type="tel"
        id="{{ $id }}"
        name="{{ $name }}"
        x-model="display"
        @input="onInput($event)"
        @focus="onFocus"
        @blur="onBlur"
        :placeholder="placeholder"
        :maxlength="maxLength"
        autocomplete="{{ $autocomplete }}"
        inputmode="tel"
        @if($required) required @endif
        class="input{{ $hasError ? ' input--error' : '' }}"
    />
    @if($error)
        <span class="field__error">{{ $error }}</span>
    @endif
</div>
