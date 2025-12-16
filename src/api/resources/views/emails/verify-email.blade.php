@component('mail::message')
# {{ $greeting }}

{{ $intro }}

@component('mail::button', ['url' => $actionUrl])
{{ $actionText }}
@endcomponent

{{ $outro }}

{{ __('mail.all_rights_reserved') }}
@endcomponent
