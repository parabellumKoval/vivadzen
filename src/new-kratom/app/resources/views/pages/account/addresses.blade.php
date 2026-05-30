@php use App\Support\Locale; @endphp

<x-layouts.app :title="__('site.account.addresses.title')" :announcement="false">
    <x-account.shell active="addresses">
        <script>window.__accountConfirmDelete = @json(__('site.account.addresses.delete_confirm'));</script>
        <div x-init="initAddresses(@js($addresses))">
            <header class="account__head">
                <h1 class="account__title">{{ __('site.account.addresses.title') }}</h1>
                <p class="account__head-hint">{{ __('site.account.addresses.head_hint') }}</p>
                <button type="button" class="btn btn--primary btn--sm" @click="newAddress()" x-show="!formOpen">
                    <x-ui.icon name="plus" :size="14" />
                    {{ __('site.account.addresses.add') }}
                </button>
            </header>

            {{-- Address form --}}
            <section class="account__card" x-show="formOpen" x-cloak>
                <form class="account__form account__form--grid" @submit.prevent="saveAddress()">
                    {{-- City picker (Meilisearch-backed) --}}
                    <div class="field account__col-2"
                         x-data="cityPicker({ initialId: form.city_id, initialLabel: form.city_label, onSelect: (id, label) => { form.city_id = id; form.city_label = label; } })"
                         x-init="$watch('form.city_id', v => { if (!v) { cityId = null; } })"
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

                    <div class="account__form-actions account__col-2">
                        <button type="submit" class="btn btn--primary btn--md" :disabled="addrBusy || !form.city_id">{{ __('site.account.addresses.save') }}</button>
                        <button type="button" class="btn btn--text btn--md" @click="formOpen = false">{{ __('site.account.addresses.cancel') }}</button>
                    </div>
                </form>
            </section>

            {{-- Address list --}}
            <p class="account__empty" x-show="addresses.length === 0 && !formOpen" x-cloak>{{ __('site.account.addresses.empty') }}</p>

            <ul class="account__addresses">
                <template x-for="a in addresses" :key="a.id">
                    <li class="account__address" :class="a.is_default && 'is-default'">
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
                                <template x-if="a.phone">
                                    <span class="account__address-phone"><br><x-ui.icon name="phone" :size="12" /> <span x-text="a.phone"></span></span>
                                </template>
                            </p>
                        </div>
                        <div class="account__address-actions">
                            <button type="button" class="account__action" x-show="!a.is_default" @click="makeDefault(a.id)"><x-ui.icon name="check" :size="14" /> {{ __('site.account.addresses.make_default') }}</button>
                            <button type="button" class="account__action" @click="editAddress(a)"><x-ui.icon name="edit" :size="14" /> {{ __('site.account.addresses.edit') }}</button>
                            <button type="button" class="account__action account__action--danger" @click="deleteAddress(a.id)"><x-ui.icon name="trash" :size="14" /> {{ __('site.account.addresses.delete') }}</button>
                        </div>
                    </li>
                </template>
            </ul>
        </div>
    </x-account.shell>
</x-layouts.app>
