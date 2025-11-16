@extends(backpack_view('blank'))

@php
    // Original jumbotron widget
    $widgets['before_content'][] = [
        'type'        => 'jumbotron',
        'heading'     => trans('backpack::base.welcome'),
        'content'     => trans('backpack::base.use_sidebar'),
        'button_link' => backpack_url('logout'),
        'button_text' => trans('backpack::base.logout'),
    ];

    // Add our custom frontend cache refresh widget
    $widgets['before_content'][] = [
        'type'         => 'view',
        'view'         => backpack_view('widgets.frontend_cache_refresh'),
        'wrapper'      => ['class' => 'col-md-12 mb-4'],
    ];
@endphp

@section('content')
@endsection