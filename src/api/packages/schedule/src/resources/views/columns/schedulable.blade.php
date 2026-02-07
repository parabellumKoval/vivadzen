{{--
Schedulable Column - displays related entity card (Product, Article, etc.)
Uses polymorphic relationship to show appropriate card based on schedulable_type
Supports HasCrudCardInterface for automatic card rendering
--}}

@php
    // Get the schedulable entity
    $schedulable = $entry->schedulable ?? null;
    
    if (!$schedulable) {
        echo '<span class="text-muted"><i class="la la-unlink"></i> Запись удалена</span>';
        return;
    }
    
    // Check if model implements HasCrudCardInterface
    $hasCrudCardInterface = $schedulable instanceof \Backpack\Schedule\Contracts\HasCrudCardInterface;
    
    if ($hasCrudCardInterface) {
        // Use the interface method to render the card
        echo $schedulable->getCrudCardHtml($column ?? []);
        return;
    }
    
    // Fallback: Check config for card view
    $schedulableType = get_class($schedulable);
    $cardsConfig = config('backpack.schedule.schedulable_cards_config', []);
    $config = $cardsConfig[$schedulableType] ?? null;
    
    if ($config && isset($config['view'])) {
        // Get edit route
        $editRoute = null;
        if (isset($config['edit_route'])) {
            $editRoute = backpack_url($config['edit_route'] . '/' . $schedulable->id . '/edit');
        }
@endphp

{{-- Render the configured card view --}}
@include($config['view'], [
    'schedulable' => $schedulable,
    'entry' => $entry,
    'editRoute' => $editRoute
])

@php
        return;
    }
    
    // Last resort fallback: Try models_list from settings
    $modelsList = \Settings::get('backpack.schedule.models_list', []);
    $modelConfig = null;
    
    foreach ($modelsList as $item) {
        if (($item['model'] ?? null) === $schedulableType) {
            $modelConfig = $item;
            break;
        }
    }
    
    // Build edit URL
    $editUrl = null;
    if ($modelConfig && isset($modelConfig['route'])) {
        $editUrl = backpack_url($modelConfig['route'] . '/' . $schedulable->id . '/edit');
    }
    
    // Get display name
    $displayName = null;
    foreach (['title', 'name', 'text'] as $field) {
        if (isset($schedulable->{$field}) && is_string($schedulable->{$field}) && strlen($schedulable->{$field}) > 0) {
            $displayName = mb_substr($schedulable->{$field}, 0, 50) . (mb_strlen($schedulable->{$field}) > 50 ? '...' : '');
            break;
        }
    }
    
    if (!$displayName) {
        $displayName = 'ID: ' . $schedulable->id;
    }
    
    // Get model name
    $modelName = $modelConfig['name'] ?? class_basename($schedulableType);
@endphp

{{-- Default fallback card --}}
<div class="schedulable-card-default" style="display: flex; align-items: center; gap: 8px; padding: 6px 0;">
    <div style="flex-shrink: 0; width: 32px; height: 32px; background: #f5f5f5; border-radius: 4px; display: flex; align-items: center; justify-content: center; border: 1px solid #e0e0e0;">
        <i class="la la-file" style="font-size: 16px; color: #999;"></i>
    </div>
    <div style="flex-grow: 1; min-width: 0;">
        <div style="font-size: 11px; color: #999; margin-bottom: 2px;">{{ $modelName }}</div>
        @if($editUrl)
            <a href="{{ $editUrl }}" style="color: #333; font-weight: 500; text-decoration: none; font-size: 13px;" title="{{ $displayName }}">
                {{ $displayName }}
            </a>
        @else
            <span style="color: #333; font-weight: 500; font-size: 13px;">{{ $displayName }}</span>
        @endif
    </div>
</div>
