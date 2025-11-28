@php
    $title = $entry->name ?? $entry->title ?? $entry->short_name ?? null;

    if (is_array($title)) {
        $title = collect($title)->filter()->first();
    }

    $subtitle = $entry->code ?? $entry->slug ?? null;
@endphp

<div class="service-card service-card--default d-flex align-items-center border rounded p-3">
    <div class="service-card__avatar mr-3">
        <div class="bg-light text-muted rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
            #{{ $entry->getKey() }}
        </div>
    </div>
    <div class="flex-grow-1">
        <div class="font-weight-bold mb-1">{{ $title ?: __('Без названия') }}</div>
        @if ($subtitle)
            <div class="text-muted small">{{ $subtitle }}</div>
        @endif
    </div>
</div>
