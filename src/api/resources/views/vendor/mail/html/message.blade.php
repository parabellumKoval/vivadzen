@component('mail::layout')
  {{-- Header --}}
  @slot('header')
    @component('mail::header', ['url' => config('app.client_url')])
      {{ config('app.name') }}
    @endcomponent
  @endslot

  {{-- Body --}}
  {{ $slot }}

  {{-- Subcopy --}}
  @isset($subcopy)
    @slot('subcopy')
      @component('mail::subcopy')
        {{ $subcopy }}
      @endcomponent
    @endslot
  @endisset


  {{-- Contacts --}}
  @isset($contacts)
    @slot('contacts')
      @component('mail::contacts')
      @endcomponent
    @endslot
  @endisset

  {{-- Footer --}}
  @slot('footer')
    @component('mail::footer')
      © {{ date('Y') }} {{ config('app.name') }}. @lang('mail.all_rights_reserved')
    @endcomponent
  @endslot
@endcomponent
