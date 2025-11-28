@php
    $title = $entry->name ?? $entry->title ?? $entry->short_name ?? null;

    if (is_array($title)) {
        $title = collect($title)->filter()->first();
    }

    $subtitle = $entry->code ?? $entry->slug ?? null;
    $score = isset($similarScore) ? round((float) $similarScore) : null;
@endphp

<div class="service-card service-card--result border rounded p-3 h-100">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <div class="font-weight-bold">{{ $title ?: __('Без названия') }}</div>
            @if ($subtitle)
                <div class="text-muted small">{{ $subtitle }}</div>
            @endif
        </div>
        @if ($score !== null)
            <span class="badge badge-info">{{ $score }}%</span>
        @endif
    </div>
    <div class="text-muted small">
        {{ __('ID записи: :id', ['id' => $entry->getKey()]) }}
    </div>
</div>
