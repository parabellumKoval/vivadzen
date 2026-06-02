@props([
    'active' => 'profile',
])

@php
    use App\Support\Locale;
    $user = auth()->user();
    $nav = [
        ['key' => 'profile',   'label' => __('site.account.nav.profile'),   'href' => Locale::url('/ucet')],
        ['key' => 'security',  'label' => __('site.account.nav.security'),  'href' => Locale::url('/ucet/zabezpeceni')],
        ['key' => 'addresses', 'label' => __('site.account.nav.addresses'), 'href' => Locale::url('/ucet/adresy')],
        ['key' => 'orders',    'label' => __('site.account.nav.orders'),    'href' => Locale::url('/ucet/objednavky')],
        ['key' => 'reviews',   'label' => __('site.account.nav.reviews'),   'href' => Locale::url('/ucet/recenze')],
        ['key' => 'forum_topics', 'label' => __('site.account.nav.forum_topics'), 'href' => Locale::url('/ucet/forum-temata')],
        ['key' => 'forum_posts', 'label' => __('site.account.nav.forum_posts'), 'href' => Locale::url('/ucet/forum-prispevky')],
    ];
@endphp

<script>
    window.__accountStrings = Object.assign(window.__accountStrings || {}, {
        addressDeleteTitle: @json(__('site.account.addresses.delete_confirm')),
        addressDeleteMessage: @json(__('site.account.addresses.delete_confirm_message')),
        reviewDeleteTitle: @json(__('site.account.reviews.delete_confirm')),
        reviewDeleteMessage: @json(__('site.account.reviews.delete_confirm_message')),
        topicDeleteTitle: @json(__('site.account.forum.topic_delete_confirm')),
        topicDeleteMessage: @json(__('site.account.forum.topic_delete_message')),
        postDeleteTitle: @json(__('site.account.forum.post_delete_confirm')),
        postDeleteMessage: @json(__('site.account.forum.post_delete_message')),
        confirmDelete: @json(__('site.account.common.delete')),
        cancel: @json(__('site.account.common.cancel')),
        confirm: @json(__('site.account.common.confirm')),
        save: @json(__('site.account.forum.save')),
        editTopicTitle: @json(__('site.account.forum.edit_topic_title')),
        editPostTitle: @json(__('site.account.forum.edit_post_title')),
    });
</script>
<div class="account" x-data="accountPage">
    <div class="container account__inner">
        <aside class="account__sidebar">
            <div class="account__user">
                <div class="account__avatar">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="" />
                    @else
                        <x-ui.icon name="user" :size="28" />
                    @endif
                </div>
                <div class="account__user-meta">
                    <p class="account__user-name">{{ $user->name }}</p>
                    <p class="account__user-email">{{ $user->email }}</p>
                </div>
            </div>

            <nav class="account__nav" aria-label="{{ __('site.account.title') }}">
                @foreach($nav as $item)
                    <a href="{{ $item['href'] }}"
                       class="account__nav-link @if($active === $item['key']) is-active @endif">
                        {{ $item['label'] }}
                    </a>
                @endforeach

                <form method="POST" action="{{ url('/auth/logout') }}" class="account__logout">
                    @csrf
                    <button type="submit" class="account__nav-link account__nav-link--logout">
                        {{ __('site.account.nav.logout') }}
                    </button>
                </form>
            </nav>
        </aside>

        <main class="account__main">
            @if(session('status'))
                <div class="account__flash" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)">
                    <x-ui.icon name="check" :size="16" /> {{ session('status') }}
                    <button type="button" @click="show = false" aria-label="{{ __('site.header.close') }}"><x-ui.icon name="x" :size="14" /></button>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>
