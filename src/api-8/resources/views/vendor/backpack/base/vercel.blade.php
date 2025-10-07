@extends(backpack_view('blank'))

@php
    $widgets['after_content'][] = [
        'type'        => 'vercel.deployments',
        'viewNamespace' => 'backpack-vercel::base.widgets',
        'heading'     => 'Vercel',
        'list' => $deployments
    ];
@endphp