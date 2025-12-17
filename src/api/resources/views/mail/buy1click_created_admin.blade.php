@component('mail::message', ['contacts' => $mailContacts ?? true])
  @component('mail::title')
  <table width="100%">
    <tr class="title-inner">
      <td class="title-number">{{ __('mail.feedback.title') }}</td>
      <td class="title-data">
        {{ optional($feedback->created_at)->format('d.m.Y H:i') }}
      </td>
    </tr>
  </table>
  @endcomponent

  <table class="order">
    <tr>
      <td class="cell-label">🙋&nbsp;&nbsp;{{ __('email.customer') ?? 'Контакты' }}:</td>
    </tr>
    <tr>
      <td class="cell-value">
        @include('mail.partials.lines', ['lines' => $contactLines ?? []])
      </td>
    </tr>
    <tr>
      <td class="cell-label">📝&nbsp;&nbsp;{{ __('email.summary') ?? 'Заявка' }}:</td>
    </tr>
    <tr>
      <td class="cell-value">
        @include('mail.partials.lines', ['lines' => $requestLines ?? []])
      </td>
    </tr>
    @if(!empty($productLines))
      <tr>
        <td class="cell-label">🛒&nbsp;&nbsp;{{ __('email.products') ?? 'Товар' }}:</td>
      </tr>
      <tr>
        <td class="cell-value">
          @include('mail.partials.lines', ['lines' => $productLines ?? []])
        </td>
      </tr>
    @endif
  </table>

  @component('mail::button', ['url' => url('/admin/feedback/'.$feedback->id.'/show')])
    {{ __('mail.feedback.button') }}
  @endcomponent
@endcomponent
