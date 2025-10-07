@extends(backpack_view('blank'))

@php
    $widgets['before_content'][] = [
        'type'        => 'jumbotron',
        'heading'     => trans('backpack::base.welcome'),
        'content'     => trans('backpack::base.use_sidebar'),
        'button_link' => backpack_url('logout'),
        'button_text' => trans('backpack::base.logout'),
    ];
@endphp


@section('content')
<div class="row">
  <div class="col-md-4">
    <div class="card">
      <div class="card-header"><strong>Vercel (frontend)</strong></div>
      <div class="card-body">
        <a href="{{ backpack_url('/vercel/redeploy') }}" class="btn btn-primary btn-block" type="button">Пересобрать frontend</a>
        <p class="text-muted mt-3">После запуска файлы сайта будут пересобраны в течение 2-3 минут.</p>
        <p>Список последних сборок и статус: <a href="{{ backpack_url('/vercel') }}">Vercel</a></p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card">
      <div class="card-header"><strong>Кеш (backend)</strong></div>
      <div class="card-body">
        <button class="btn btn-secondary btn-block" type="button">Сбросить кеш</button>
        <p class="text-muted mt-3">Старый кеш будет сброшен, данные будут перекешированы по-новой</p>
      </div>
    </div>
  </div>
  <!-- /.col-->
  <!-- /.col-->
</div>
@endsection