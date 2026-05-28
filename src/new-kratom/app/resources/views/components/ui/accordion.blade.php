@props([
    'items' => [],  // [['question' => '...', 'answer' => '...']]
])

<div class="accordion">
    @foreach($items as $item)
        <details class="accordion__item">
            <summary class="accordion__summary">
                <span>{{ $item['question'] }}</span>
                <span class="accordion__icon"><x-ui.icon name="chevron-down" /></span>
            </summary>
            <div class="accordion__panel">
                {{-- Текст ответа всегда в DOM для SEO. Аккордеон только визуально скрывает. --}}
                {!! $item['answer'] !!}
            </div>
        </details>
    @endforeach
</div>
