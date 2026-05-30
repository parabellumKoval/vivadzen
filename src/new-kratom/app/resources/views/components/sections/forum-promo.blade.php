{{-- Forum promo (homepage). Небольшая секция, ведёт на /forum. --}}
@php
    use App\Support\Forum;
    use App\Support\Locale;
    $featured = array_slice(Forum::topTopics(3), 0, 3);
    $stats = Forum::stats();
@endphp

<section class="section forum-promo" aria-labelledby="forum-promo-title">
    <div class="container forum-promo__inner">
        <div class="forum-promo__copy">
            <p class="t-overline section-head__eyebrow--grass">FORUM KRATOMISTŮ</p>
            <h2 id="forum-promo-title" class="t-heading-xl t-on-dark mt-3">Komunita, která ví, o čem mluví</h2>
            <p class="forum-promo__lead">
                Sdílejte zkušenosti s odrůdami, přípravou a dávkováním. Ptejte se těch, kdo kratom
                znají roky. Bez reklam, jen kvalitní debata.
            </p>

            <ul class="forum-promo__stats" role="list">
                <li><strong>{{ number_format($stats['topics']) }}</strong><span>diskuzí</span></li>
                <li><strong>{{ number_format($stats['posts']) }}</strong><span>příspěvků</span></li>
                <li><strong>{{ number_format($stats['members']) }}</strong><span>členů</span></li>
            </ul>

            <div class="forum-promo__cta">
                <x-ui.button href="{{ Locale::url('/forum') }}" variant="primary" icon="arrow-right">
                    Vstoupit do fóra
                </x-ui.button>
                <x-ui.button href="{{ Locale::url('/forum/nove-tema') }}" variant="secondary" icon="plus" iconPosition="left">
                    Otevřít nové téma
                </x-ui.button>
            </div>
        </div>

        <div class="forum-promo__preview" aria-label="Populární diskuze">
            @foreach($featured as $t)
                <a href="{{ Locale::url('/forum/tema/' . $t['slug']) }}" class="forum-promo__pcard">
                    <span class="forum-promo__pemoji" aria-hidden="true">{{ $t['emoji'] }}</span>
                    <div>
                        <p class="forum-promo__ptitle">{{ $t['title'] }}</p>
                        <p class="forum-promo__pmeta">{{ $t['replies'] }} komentářů · {{ $t['views'] }} zobrazení</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
