@php use App\Support\Locale; @endphp

<x-layouts.app :title="__('site.account.title')" :announcement="false">
    <x-account.shell active="profile">
        <div x-init="initAvatar(@js($user->avatar_url))">
            <header class="account__head">
                <h1 class="account__title">{{ __('site.account.profile.title') }}</h1>
                <p class="account__head-hint">{{ __('site.account.profile.head_hint') }}</p>
            </header>

            {{-- Avatar --}}
            <section class="account__card">
                <div class="account__avatar-row">
                    <div class="account__avatar account__avatar--lg">
                        <template x-if="avatarUrl">
                            <img :src="avatarUrl" alt="" />
                        </template>
                        <template x-if="!avatarUrl">
                            <x-ui.icon name="user" :size="40" />
                        </template>
                    </div>

                    <div class="account__avatar-actions">
                        <p class="account__label">{{ __('site.account.profile.avatar') }}</p>
                        <p class="account__hint">{{ __('site.account.profile.avatar_hint') }}</p>
                        <div class="account__avatar-buttons">
                            <label class="btn btn--outline-light btn--sm" :class="avatarBusy && 'is-busy'">
                                <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="uploadAvatar($event)" />
                                <x-ui.icon name="upload" :size="14" />
                                {{ __('site.account.profile.upload') }}
                            </label>
                            <button type="button" class="btn btn--text btn--sm" x-show="avatarUrl" @click="removeAvatar()">
                                {{ __('site.account.profile.remove') }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Personal details --}}
            <section class="account__card">
                <form method="POST" action="{{ Locale::url('/ucet/profil') }}" class="account__form">
                    @csrf

                    <div class="field">
                        <label class="field__label" for="acc-name">{{ __('site.account.profile.name') }}</label>
                        <input id="acc-name" name="name" type="text" class="input @error('name') input--error @enderror"
                               value="{{ old('name', $user->name) }}" required />
                        <p class="field__helper">{{ __('site.account.profile.name_helper') }}</p>
                        @error('name')<span class="field__error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="acc-phone">{{ __('site.account.profile.phone') }}</label>
                        <x-form.phone
                            id="acc-phone"
                            name="phone"
                            :value="old('phone', $user->phone)"
                            :error="$errors->first('phone')"
                            region="cz"
                        />
                        <p class="field__helper">{{ __('site.account.profile.phone_helper') }}</p>
                    </div>

                    <div class="field">
                        <label class="field__label" for="acc-forum-signature">{{ __('site.account.profile.forum_signature') }}</label>
                        <textarea
                            id="acc-forum-signature"
                            name="forum_signature"
                            class="input @error('forum_signature') input--error @enderror"
                            rows="3"
                            maxlength="220"
                            placeholder="{{ __('site.account.profile.forum_signature_placeholder') }}"
                        >{{ old('forum_signature', $user->forum_signature) }}</textarea>
                        <p class="field__helper">{{ __('site.account.profile.forum_signature_helper') }}</p>
                        @error('forum_signature')<span class="field__error">{{ $message }}</span>@enderror
                    </div>

                    <label class="account__check">
                        <input type="checkbox" name="marketing_consent" value="1" @checked(old('marketing_consent', $user->marketing_consent)) />
                        <span>{{ __('site.account.profile.marketing') }}</span>
                    </label>

                    <div class="account__form-actions">
                        <button type="submit" class="btn btn--primary btn--md">{{ __('site.account.profile.save') }}</button>
                    </div>
                </form>
            </section>

            {{-- Linked social accounts --}}
            @if($user->socialAccounts->isNotEmpty())
                <section class="account__card">
                    <h2 class="account__subtitle">{{ __('site.account.profile.linked_accounts') }}</h2>
                    <ul class="account__linked">
                        @foreach($user->socialAccounts as $sa)
                            <li class="account__linked-item">
                                <span class="account__linked-provider">{{ ucfirst($sa->provider) }}</span>
                                <x-ui.icon name="badge-check" :size="16" />
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <p class="account__meta-note">
                {{ __('site.account.profile.member_since', ['date' => $user->created_at->isoFormat('LL')]) }}
            </p>
        </div>
    </x-account.shell>
</x-layouts.app>
