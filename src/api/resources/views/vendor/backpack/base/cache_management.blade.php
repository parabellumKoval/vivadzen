@extends(backpack_view('blank'))

@php
    $breadcrumbs = [
        trans('backpack::crud.admin') => backpack_url('dashboard'),
        'Кеширование'                 => false,
    ];

    $widgets['before_content'][] = [
        'type'    => 'view',
        'view'    => 'webhooks::widgets.frontend_cache_refresh',
        'wrapper' => ['class' => ''],
    ];
@endphp

@section('header')
<div class="container-fluid mb-4">
    <h2 class="mb-1">
        <span class="text-capitalize">Кеширование</span>
    </h2>
    <small class="text-muted">Управление прогревом фронтенда и обновлением статического кеша</small>
</div>
@endsection

@section('content')
@endsection
