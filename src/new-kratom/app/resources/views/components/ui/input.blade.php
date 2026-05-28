@props([
    'name' => null,
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'label' => null,
    'helper' => null,
    'error' => null,
    'required' => false,
    'onDark' => false,
    'pill' => false,
    'labelOnDark' => false,
])

@php
    $id = $attributes->get('id') ?: $name;
    $inputClasses = collect([
        'input',
        $onDark ? 'input--on-dark' : null,
        $pill ? 'input--pill' : null,
        $error ? 'input--error' : null,
    ])->filter()->implode(' ');
@endphp

<div class="field">
    @if($label)
        <label for="{{ $id }}" class="field__label @if($labelOnDark) field__label--on-dark @endif">
            {{ $label }}@if($required)<span class="field__required">*</span>@endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        id="{{ $id }}"
        @if($name) name="{{ $name }}" @endif
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        {{ $attributes->except(['id'])->class($inputClasses) }}
    />

    @if($helper && !$error)
        <span class="field__helper">{{ $helper }}</span>
    @endif

    @if($error)
        <span class="field__error">
            <x-ui.icon name="x" :size="14" /> {{ $error }}
        </span>
    @endif
</div>
