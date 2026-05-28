@props([
    'title' => '',
    'paragraphs' => [],   // string[]
    'imageLabel' => 'Mitragyna speciosa list',
    'imageCaption' => '',
])

<section class="cat-about" aria-labelledby="cat-about-title">
    <div class="container">
        <div class="cat-about__card">
            <div class="cat-about__copy">
                <h2 id="cat-about-title" class="t-heading-xl t-on-light-accent">{{ $title }}</h2>
                @foreach($paragraphs as $p)
                    <p class="cat-about__p t-body-md">{{ $p }}</p>
                @endforeach
            </div>
            <figure class="cat-about__figure">
                <x-ui.placeholder shape="wide" :label="$imageLabel" icon="leaf" />
                @if($imageCaption)
                    <figcaption class="cat-about__caption">{{ $imageCaption }}</figcaption>
                @endif
            </figure>
        </div>
    </div>
</section>
