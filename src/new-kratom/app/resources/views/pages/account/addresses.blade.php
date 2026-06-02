@php use App\Support\Locale; @endphp

<x-layouts.app :title="__('site.account.addresses.title')" :announcement="false">
    <x-account.shell active="addresses">
        <script>window.__accountConfirmDelete = @json(__('site.account.addresses.delete_confirm'));</script>

        <div x-init="initAddresses(@js($addresses))">
            <header class="account__head">
                <div class="account__head-text">
                    <h1 class="account__title">{{ __('site.account.addresses.title') }}</h1>
                    <p class="account__head-hint">{{ __('site.account.addresses.head_hint') }}</p>
                </div>
                <button type="button" class="btn btn--primary btn--sm" @click="newAddress()">
                    <x-ui.icon name="plus" :size="14" />
                    {{ __('site.account.addresses.add') }}
                </button>
            </header>

            {{-- Empty state --}}
            <div class="account__empty-card" x-show="addresses.length === 0" x-cloak>
                <x-ui.icon name="map-pin" :size="40" />
                <p class="account__empty-title">{{ __('site.account.addresses.empty_title') }}</p>
                <p class="account__empty-text">{{ __('site.account.addresses.empty') }}</p>
                <button type="button" class="btn btn--primary btn--md" @click="newAddress()">
                    <x-ui.icon name="plus" :size="14" />
                    {{ __('site.account.addresses.add') }}
                </button>
            </div>

            {{-- Address list --}}
            <ul class="account__addresses" x-show="addresses.length > 0" x-cloak>
                <template x-for="a in addresses" :key="a.id">
                    <li class="account__address" :class="a.is_default && 'is-default'">
                        <button
                            type="button"
                            class="account__address-star"
                            :class="a.is_default && 'is-on'"
                            :aria-label="a.is_default ? '{{ __('site.account.addresses.default') }}' : '{{ __('site.account.addresses.make_default') }}'"
                            :title="a.is_default ? '{{ __('site.account.addresses.default') }}' : '{{ __('site.account.addresses.make_default') }}'"
                            @click="toggleDefault(a)"
                        >
                            <x-ui.icon name="star" :size="18" />
                        </button>

                        <div class="account__address-body">
                            <p class="account__address-head">
                                <span class="account__address-name" x-text="a.city?.full_label || ''"></span>
                                <template x-if="a.is_default">
                                    <span class="account__badge account__badge--ok">{{ __('site.account.addresses.default') }}</span>
                                </template>
                            </p>
                            <p class="account__address-lines">
                                <span x-text="a.street"></span>
                                <template x-if="a.city?.region_name">
                                    <span class="account__address-region">, <span x-text="a.city.region_name"></span></span>
                                </template>
                            </p>
                            <template x-if="a.phone">
                                <p class="account__address-phone">
                                    <x-ui.icon name="phone" :size="12" />
                                    <span x-text="a.phone"></span>
                                </p>
                            </template>
                        </div>

                        <div class="account__address-actions">
                            <button type="button" class="account__btn-icon" @click="editAddress(a)">
                                <x-ui.icon name="edit" :size="14" />
                                <span>{{ __('site.account.addresses.edit') }}</span>
                            </button>
                            <button type="button" class="account__btn-icon account__btn-icon--danger" @click="deleteAddress(a)">
                                <x-ui.icon name="trash" :size="14" />
                                <span>{{ __('site.account.addresses.delete') }}</span>
                            </button>
                        </div>
                    </li>
                </template>
            </ul>

            {{-- Address modal — centered. CSS-only visibility off the `.is-open` flag. --}}
            <div
                class="address-modal"
                :class="formOpen && 'is-open'"
                @keydown.escape.window="formOpen = false"
            >
                <div class="address-modal__backdrop" @click="formOpen = false"></div>

                <div
                    class="address-modal__panel"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="address-modal-title"
                >
                    <header class="address-modal__head">
                        <h2 id="address-modal-title" class="address-modal__title"
                            x-text="editing
                                ? '{{ __('site.account.addresses.edit_title') }}'
                                : '{{ __('site.account.addresses.add_title') }}'"></h2>
                        <button type="button" class="address-modal__close" @click="formOpen = false"
                                aria-label="{{ __('site.header.close') }}">
                            <x-ui.icon name="x" :size="18" />
                        </button>
                    </header>

                    <form class="address-modal__body account__form account__form--grid"
                          @submit.prevent="saveAddress()">
                        {{-- City picker (Meilisearch-backed) --}}
                        <div class="field account__col-2"
                             x-data="cityPicker({ initialId: form.city_id, initialLabel: form.city_label, onSelect: (id, label) => { form.city_id = id; form.city_label = label; } })"
                             x-init="$watch('form.city_id', v => { if (!v) { cityId = null; query = ''; } })"
                             @click.outside="open = false">
                            <label class="field__label" for="addr-city">{{ __('site.account.addresses.city') }} *</label>
                            <div class="city-picker">
                                <input
                                    id="addr-city"
                                    type="text"
                                    class="input"
                                    :class="addrError('city_id') && 'input--error'"
                                    x-model="query"
                                    @input="onInput()"
                                    @focus="onFocus()"
                                    @blur="onBlur()"
                                    @keydown="onKeydown($event)"
                                    autocomplete="off"
                                    placeholder="{{ __('site.account.addresses.city_placeholder') }}"
                                    required
                                />
                                <div class="city-picker__menu" x-show="open" x-cloak>
                                    <template x-for="(o, i) in options" :key="o.id">
                                        <button
                                            type="button"
                                            class="city-picker__option"
                                            :class="i === activeIndex && 'is-active'"
                                            @mousedown.prevent="pick(o)"
                                            @mouseenter="activeIndex = i"
                                        >
                                            <span class="city-picker__name" x-text="o.name"></span>
                                            <span class="city-picker__meta" x-show="o.district_name">
                                                <span x-text="o.district_name"></span>
                                                <template x-if="o.region_name && o.region_name !== o.district_name">
                                                    <span class="city-picker__meta-sep">, <span x-text="o.region_name"></span></span>
                                                </template>
                                            </span>
                                        </button>
                                    </template>
                                    <p class="city-picker__empty" x-show="!loading && options.length === 0">{{ __('site.account.addresses.city_no_results') }}</p>
                                </div>
                            </div>
                            <span class="field__error" x-show="addrError('city_id')" x-text="addrError('city_id')"></span>
                            <p class="field__helper">{{ __('site.account.addresses.city_helper') }}</p>
                        </div>

                        <div class="field account__col-2">
                            <label class="field__label" for="addr-street">{{ __('site.account.addresses.street') }} *</label>
                            <input id="addr-street" type="text" class="input" x-model="form.street"
                                   :class="addrError('street') && 'input--error'"
                                   placeholder="{{ __('site.account.addresses.street_placeholder') }}" required />
                            <span class="field__error" x-show="addrError('street')" x-text="addrError('street')"></span>
                        </div>

                        <div class="field account__col-2">
                            <label class="field__label" for="addr-phone">{{ __('site.account.addresses.phone') }}</label>
                            <x-form.phone id="addr-phone" name="address-phone" x-model="form.phone" region="cz" />
                            <p class="field__helper">{{ __('site.account.addresses.phone_helper') }}</p>
                        </div>

                        <label class="account__check account__col-2">
                            <input type="checkbox" x-model="form.is_default" />
                            <span>{{ __('site.account.addresses.make_default') }}</span>
                        </label>

                        <div class="address-modal__actions account__col-2">
                            <button type="button" class="btn btn--ghost btn--md" @click="formOpen = false">{{ __('site.account.addresses.cancel') }}</button>
                            <button type="submit" class="btn btn--primary btn--md" :disabled="addrBusy || !form.city_id">{{ __('site.account.addresses.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-account.shell>
</x-layouts.app>
