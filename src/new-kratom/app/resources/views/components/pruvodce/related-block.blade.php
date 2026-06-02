@php use App\Support\Locale; @endphp
@props(['articles'])

@if ($articles->isNotEmpty())
    <aside class="container container--narrow pruvodce-related">
        <h2 class="pruvodce-related__title">Související články</h2>
        <ul class="pruvodce-related__list">
            @foreach ($articles as $r)
                <li>
                    <a href="{{ Locale::url('/pruvodce/'.$r->category->slug.'/'.$r->slug) }}">
                        <span class="pruvodce-related__cat">{{ $r->category->title }}</span>
                        <span class="pruvodce-related__heading">{{ $r->title }}</span>
                        @if ($r->excerpt)
                            <span class="pruvodce-related__desc">{{ $r->excerpt }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </aside>
@endif
