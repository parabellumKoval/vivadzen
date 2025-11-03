@php
  $lines = $lines ?? [];
@endphp

@if(!empty($lines))
  @foreach($lines as $line)
    <div class="line">{!! $line !!}</div>
  @endforeach
@else
  <div class="line">{{ __('email.none') }}</div>
@endif
