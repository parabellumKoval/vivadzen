<x-layouts.app
    title="{{ __('site.adulto.page_title') }} | Vivadzen"
    description="{{ __('site.adulto.page_description') }}"
>
    <x-static.hero
        icon="shield-check"
        :eyebrow="__('site.adulto.eyebrow')"
        :title="__('site.adulto.page_title')"
        :lead="__('site.adulto.page_lead')"
    />

    <section class="static-section adulto-page">
        <div class="container">
            <div class="adulto-page__grid">
                <div class="adulto-page__main">
                    <x-static.adulto-guide version="full" :show-extras="false" />
                </div>

                <div class="adulto-page__sidebar">
                    <x-static.adulto-extras version="full" layout="aside" />
                </div>
            </div>
        </div>
    </section>

    <x-static.cta />
</x-layouts.app>
