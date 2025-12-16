@php
  $details = $details ?? [];
  $address = trim((string) ($details['address'] ?? __('contacts.address')));
  $phone = trim((string) ($details['phone'] ?? __('contacts.phone')));
  $email = trim((string) ($details['email'] ?? __('contacts.email')));
  $phoneHref = $phone !== '' ? preg_replace('/\s+/', '', $phone) : null;
  $emailHref = $email !== '' ? $email : null;
  $social = $details['social'] ?? [];
  if (!is_array($social)) {
      $social = [];
  }
  $social = array_filter($social, fn ($value) => is_string($value) && trim($value) !== '');
  $socialIcons = [
      'instagram' => 'instagram',
      'viber' => 'viber',
      'telegram' => 'telegram',
      'whatsapp' => 'whatsapp',
  ];
@endphp
<tr>
  <td class="contacts">
    <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
      <tr>
        <td class="content-cell" align="center">
          <table class="contacts-inner" width="100%">
            <tr>
              <td colspan="2" class="cell-label">📌&nbsp;&nbsp;@lang('email.our_address'):</td>
            </tr>
            <tr>
              <td colspan="2" class="cell-value">{{ $address }}</td>
            </tr>
            @if($phone || $email)
            <tr>
              <td>
                <table>
                  <tr>
                    <td class="cell-label">📞&nbsp;&nbsp;@lang('email.phone'):</td>
                  </tr>
                  <tr>
                    <td class="cell-value">
                      @if($phone)
                        <a href="tel:{{ e($phoneHref) }}" class="contact-link">{{ $phone }}</a>
                      @else
                        &mdash;
                      @endif
                    </td>
                  </tr>
                </table>
              </td>
              <td>
                <table>
                  <tr>
                    <td class="cell-label">📬&nbsp;&nbsp;@lang('email.email'):</td>
                  </tr>
                  <tr>
                    <td class="cell-value">
                      @if($email)
                        <a href="mailto:{{ e($emailHref) }}" class="contact-link">{{ $email }}</a>
                      @else
                        &mdash;
                      @endif
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
            @endif
            @if(!empty($social))
            <tr>
              <td colspan="2" class="cell-label">@lang('email.messangers'):</td>
            </tr>
            <tr>
              <td colspan="2" class="">
                @foreach($socialIcons as $network => $icon)
                  @if(!empty($social[$network]))
                    <a href="{{ e($social[$network]) }}" class="social-btn {{ $network }}">
                      <img src="{{ url('/sys/icon/' . $icon . '.png') }}" class="icon"/>
                    </a>
                  @endif
                @endforeach
              </td>
            </tr>
            @endif
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>
