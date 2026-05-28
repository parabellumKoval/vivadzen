@props([
    'message' => 'EXPRESS doručení do 180 minut v Praze a Ostravě',
    'link' => '/doruceni',
    'linkLabel' => 'Podrobnosti',
])

<aside
    class="announce-bar"
    x-data="announcementBar"
    x-show="visible"
    x-transition.opacity
    role="region"
    aria-label="Oznámení"
    style="position: relative;"
>
    <div class="announce-bar__inner">
        <x-ui.icon name="zap" :size="16" />
        <span>{{ $message }} · <a href="{{ $link }}" class="t-on-light">{{ $linkLabel }} →</a></span>
    </div>
    <button
        type="button"
        class="announce-bar__close"
        @click="close()"
        aria-label="Skrýt oznámení"
    >
        <x-ui.icon name="x" :size="18" />
    </button>
</aside>
