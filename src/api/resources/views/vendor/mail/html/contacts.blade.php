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
              <td colspan="2" class="cell-value">@lang('contacts.address')</td>
            </tr>
            <tr>
              <td>
                <table>
                  <tr>
                    <td class="cell-label">📞&nbsp;&nbsp;@lang('email.phone'):</td>
                  </tr>
                  <tr>
                    <td class="cell-value">
                      <a href="tel:{{ __('contacts.phone') }}" class="contact-link">@lang('contacts.phone')</a>  
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
                      <a href="mailto:{{ __('contacts.email') }}" class="contact-link">@lang('contacts.email')</a>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td colspan="2" class="cell-label">@lang('email.messangers'):</td>
            </tr>
            <tr>
              <td colspan="2" class="">
                <!-- <a href="{{ __('contacts.facebook') }}" class="social-btn facebook">
                  <img src="{{ url('/uploads/images/social/facebook.png') }}" class="icon"/>
                </a> -->
                <a href="{{ __('contacts.instagram') }}" class="social-btn instagram">
                  <img src="{{ url('/uploads/images/social/instagram.png') }}" class="icon"/>
                </a>
                <!-- <a href="{{ __('contacts.tiktok') }}" class="social-btn tiktok">
                  <img src="{{ url('/uploads/images/social/tiktok.png') }}" class="icon"/>
                </a>
                <a href="{{ __('contacts.whatsapp') }}" class="social-btn whatsapp">
                  <img src="{{ url('/uploads/images/social/whatsapp.png') }}" class="icon"/>
                </a> -->
                <a href="{{ __('contacts.viber') }}" class="social-btn viber">
                  <img src="{{ url('/uploads/images/social/viber.png') }}" class="icon"/>
                </a>
                <a href="{{ __('contacts.telegram') }}" class="social-btn telegram">
                  <img src="{{ url('/uploads/images/social/telegram.png') }}" class="icon"/>
                </a>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>