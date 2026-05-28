@props([
    'items' => [],   // [['label' => 'Domů', 'href' => '/'], …]
])

<nav class="breadcrumbs" aria-label="Drobečková navigace">
    <div class="container">
        <ol class="breadcrumbs__list">
            @foreach($items as $i => $item)
                <li class="breadcrumbs__item">
                    @if(!empty($item['href']) && !$loop->last)
                        <a href="{{ $item['href'] }}" class="breadcrumbs__link">{{ $item['label'] }}</a>
                    @else
                        <span class="breadcrumbs__current" aria-current="page">{{ $item['label'] }}</span>
                    @endif

                    @unless($loop->last)
                        <span class="breadcrumbs__sep" aria-hidden="true">›</span>
                    @endunless
                </li>
            @endforeach
        </ol>
    </div>
</nav>
