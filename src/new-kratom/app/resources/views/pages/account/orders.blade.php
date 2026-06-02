@php use App\Support\Locale; @endphp

<x-layouts.app :title="__('site.account.orders.title')" :announcement="false">
    <x-account.shell active="orders">
        <script>window.__accountStatuses = @json(__('site.account.statuses'));</script>
        <header class="account__head">
            <div class="account__head-text">
                <h1 class="account__title">{{ __('site.account.orders.title') }}</h1>
                <p class="account__head-hint">{{ __('site.account.orders.head_hint') }}</p>
            </div>
        </header>

        @if($orders->isEmpty())
            <div class="account__empty-card">
                <x-ui.icon name="package" :size="40" />
                <p class="account__empty-title">{{ __('site.account.orders.empty_title') }}</p>
                <p class="account__empty">{{ __('site.account.orders.empty') }}</p>
                <a href="{{ \App\Support\Locale::url('/kratom') }}" class="btn btn--primary btn--md">
                    {{ __('site.account.orders.empty_cta') }}
                </a>
            </div>
        @else
            <section class="account__card account__card--flush">
                <table class="account__table">
                    <thead>
                        <tr>
                            <th>{{ __('site.account.orders.order') }}</th>
                            <th>{{ __('site.account.orders.date') }}</th>
                            <th>{{ __('site.account.orders.status') }}</th>
                            <th class="account__table-num">{{ __('site.account.orders.items') }}</th>
                            <th class="account__table-num">{{ __('site.account.orders.total') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td class="account__mono">{{ $order->public_id }}</td>
                                <td>{{ $order->created_at->isoFormat('LL') }}</td>
                                <td>
                                    <span class="account__status account__status--{{ $order->status }}">
                                        {{ __('site.account.statuses.'.$order->status) }}
                                    </span>
                                </td>
                                <td class="account__table-num">{{ $order->items_count }}</td>
                                <td class="account__table-num">{{ $order->total }} {{ __('site.currency') }}</td>
                                <td class="account__table-num">
                                    <button type="button" class="account__action" @click="openOrder('{{ $order->public_id }}')">
                                        {{ __('site.account.orders.view') }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <div class="account__pagination">
                {{ $orders->links() }}
            </div>
        @endif

        {{-- Order detail modal --}}
        <div class="account-modal" x-show="orderOpen" x-cloak @keydown.escape.window="closeOrder()">
            <div class="account-modal__backdrop" @click="closeOrder()"></div>
            <div class="account-modal__panel" role="dialog" aria-modal="true">
                <header class="account-modal__head">
                    <h2 class="account-modal__title">
                        {{ __('site.account.orders.detail') }}
                        <span class="account__mono" x-text="order?.public_id"></span>
                    </h2>
                    <button type="button" class="account-modal__close" @click="closeOrder()" aria-label="{{ __('site.account.orders.close') }}">
                        <x-ui.icon name="x" />
                    </button>
                </header>

                <div class="account-modal__body">
                    <template x-if="orderLoading">
                        <p class="account__hint">…</p>
                    </template>

                    <template x-if="order && !orderLoading">
                        <div>
                            <p class="account-modal__status">
                                <span class="account__status" :class="'account__status--' + order.status" x-text="statusLabel(order.status)"></span>
                            </p>

                            <ul class="account-modal__items">
                                <template x-for="item in order.items" :key="item.id">
                                    <li class="account-modal__item">
                                        <span class="account-modal__item-name">
                                            <span x-text="item.product_name"></span>
                                            <span class="account-modal__item-size" x-text="item.size + (item.unit || 'g') + ' × ' + item.qty"></span>
                                        </span>
                                        <span class="account-modal__item-price" x-text="item.line_total + ' {{ __('site.currency') }}'"></span>
                                    </li>
                                </template>
                            </ul>

                            <dl class="account-modal__totals">
                                <div><dt>{{ __('site.account.orders.subtotal') }}</dt><dd><span x-text="order.subtotal"></span> {{ __('site.currency') }}</dd></div>
                                <div x-show="order.discount > 0"><dt>{{ __('site.account.orders.discount') }}</dt><dd>−<span x-text="order.discount"></span> {{ __('site.currency') }}</dd></div>
                                <div x-show="order.shipping > 0"><dt>{{ __('site.account.orders.shipping') }}</dt><dd><span x-text="order.shipping"></span> {{ __('site.currency') }}</dd></div>
                                <div class="account-modal__total"><dt>{{ __('site.account.orders.total') }}</dt><dd><strong><span x-text="order.total"></span> {{ __('site.currency') }}</strong></dd></div>
                            </dl>

                            <div class="account-modal__grid">
                                <div>
                                    <h3 class="account-modal__subhead">{{ __('site.account.orders.delivery') }}</h3>
                                    <p class="account-modal__address">
                                        <span x-text="order.first_name + ' ' + order.last_name"></span><br>
                                        <span x-text="order.street"></span><br>
                                        <span x-text="order.zip + ' ' + order.city + ', ' + order.country"></span><br>
                                        <span x-text="order.phone"></span>
                                    </p>
                                </div>
                                <div>
                                    <h3 class="account-modal__subhead">{{ __('site.account.orders.payment') }}</h3>
                                    <p class="account-modal__address">
                                        <span x-text="order.payment_method"></span> · <span x-text="order.delivery_method"></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </x-account.shell>
</x-layouts.app>
