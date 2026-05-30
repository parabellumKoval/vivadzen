{{--
    Главная страница (homepage)
    Соответствует 04_STRANKA_HOMEPAGE.md — секции S0..S13.

    S0 AnnouncementBar — в layout
    S1 Header           — в layout (transparent=true для прозрачного старта)
    S2 AgeStrip         — в layout
    S3 Hero             — sections/hero
    S4 TrustBar         — sections/trust-bar
    S5 Categories       — sections/categories
    S6 Bestsellers      — sections/bestsellers
    S7 Why-Vivadzen     — sections/why-vivadzen
    S8+S9 Reviews       — sections/reviews-showcase (заменил google-reviews + trusted)
    S10 Content Hub     — sections/content-hub
    S11 FAQ             — sections/faq (+ FAQPage JSON-LD)
    S12 Newsletter      — sections/newsletter
    S13 Footer          — в layout
--}}
<x-layouts.app
    :transparentHeader="true"
    title="Kratom s licencí MZ ČR — laboratorně testovaný | Vivadzen"
    description="Specializovaný e-shop kratomu pod licencí PML. Každá šarže testovaná VŠCHT Praha. 2 prodejny v Praze. Doručení do 180 min v Praze a Ostravě."
>
    <x-sections.hero />
    <x-sections.trust-bar />
    <x-sections.categories />
    <x-sections.bestsellers />
    <x-sections.why-vivadzen />
    <x-sections.reviews-showcase />
    <x-sections.content-hub />
    <x-sections.forum-promo />
    <x-sections.faq />
    <x-sections.newsletter />

    @push('schema')
        <script type="application/ld+json">
        @verbatim
        {
          "@context": "https://schema.org",
          "@type": "Organization",
          "name": "Vivadzen",
          "url": "https://vivadzen.com",
          "logo": "https://vivadzen.com/assets/brand/logo-vivadzen-primary-light.avif",
          "description": "Licencovaný specializovaný e-shop kratomu pod režimem PML.",
          "sameAs": [
            "https://www.facebook.com/vivadzen",
            "https://www.instagram.com/vivadzen"
          ]
        }
        @endverbatim
        </script>
    @endpush
</x-layouts.app>
