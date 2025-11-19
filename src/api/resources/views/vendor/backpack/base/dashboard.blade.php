@extends(backpack_view('blank'))

@php
    $profileWidgetData = app(\Backpack\Profile\app\Services\DashboardWidgetData::class)->get();
    $topUsers = $profileWidgetData['topUsers'] ?? collect();
    $topUsersSort = $profileWidgetData['topUsersSort'] ?? 'created';
    $referralLeaders = $profileWidgetData['referralLeaders'] ?? collect();

    /** @var \Backpack\Store\app\Services\AdminDashboardWidgetService $storeDashboard */
    $storeDashboard = app(\Backpack\Store\app\Services\AdminDashboardWidgetService::class);
    $bestSellingProducts = $storeDashboard->bestSellingProducts();
    $ordersWidget = $storeDashboard->ordersWidgetPayload();
    $ordersCountries = $storeDashboard->ordersByCountryStats();
@endphp

@section('content')
    <div class="row">
        <div class="col-xl-10 mb-4">
            @include('profile-backpack::widgets.profile.users_referrals', ['topUsers' => $topUsers, 'activeSort' => $topUsersSort])
        </div>
        <div class="col-xl-2 mb-4">
            @include('profile-backpack::widgets.profile.referral_leaders', ['leaders' => $referralLeaders])
        </div>
    </div>

    <div class="row">
        <div class="col-xl-7 mb-4">
            @include('backpack-store::widgets.dashboard.orders_table', [
                'orders'         => $ordersWidget['orders'],
                'countries'      => $ordersWidget['countries'],
                'activeCountry'  => $ordersWidget['active'] ?? null,
                'ordersEndpoint' => route('backpack.store.dashboard.orders'),
            ])
        </div>
        <div class="col-xl-5 mb-4">
            @include('backpack-store::widgets.dashboard.top_selling_products', [
                'products' => $bestSellingProducts,
            ])
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            @include('backpack-store::widgets.dashboard.orders_countries_chart', [
                'chart' => $ordersCountries['chart'] ?? [],
                'countries' => $ordersCountries['countries'] ?? [],
                'totalOrders' => $ordersCountries['totalOrders'] ?? 0,
                'range' => $ordersCountries['range'] ?? [],
            ])
        </div>
    </div>
@endsection
