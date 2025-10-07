<div class="admin-props">
    @if($specs && is_array($specs))
        @php
            $specs_count = 0;

            $specs_html = '<ul>';
            foreach($specs as $key => $value) {
                if((bool)$value) {
                  $specs_count++;
                  $translated_label = trans('specs.' . $key);
                  $specs_html .= "<li><b>{$translated_label}</b></li>";
                }
            }
            $specs_html .= '</ul>';
        @endphp

        @if($specs_count)
            <span class='admin-props-item admin-props-specs' data-placement='left' data-toggle='tooltip' data-html='true' title data-original-title='<div class="admin-props-specs-tooltip">{!! $specs_html !!}</div>'>
                <i class='las la-wine-bottle admin-props-icon'></i> {{ $specs_count }}/6
            </span>
        @endif
    @endif

    @if($customProperties && !empty($customProperties))
        @php
          $cp_count = count($customProperties);
          $props_html = '<ul>';
            foreach($customProperties as $key => $value) {
              $escaped_name = e($value['name']);
              $escaped_value = e($value['value']);
              $props_html .= "<li><b>{$escaped_name}</b>: {$escaped_value}</li>";
            }
          $props_html .= '</ul>';
        @endphp
        @if($cp_count)
            <span class='admin-props-item' data-placement='left' data-toggle='tooltip' data-html='true' title data-original-title='<div class="admin-props-specs-tooltip">{!! $props_html !!}</div>'>
                <i class='las la-list admin-props-icon'></i> {{ $cp_count }}
            </span>
        @endif
    @endif

    @if($properties && !empty($properties))
        @php
          $p_count = count($properties);

          $attrs_html = '<ul>';
            foreach($properties as $key => $value) {
              if(is_array($value['value'])) {
                $values = [];
                foreach($value['value'] as $item) {
                  if($item && !empty($item['value'])) {
                    $values[] = e($item['value']);
                  }
                }
                $str_value = implode('; ', $values);
              }else {
                $str_value = e($value['value']);
              }

              $escaped_name = e($value['name']);
              $attrs_html .= "<li><b>{$escaped_name}</b>: {$str_value}</li>";
            }
          $attrs_html .= '</ul>';
        @endphp
        @if($p_count)
            <span class='admin-props-item' data-placement='left' data-toggle='tooltip' data-html='true' title data-original-title='<div class="admin-props-specs-tooltip">{!! $attrs_html !!}</div>'>
                <i class='las la-filter admin-props-icon'></i> {{ $p_count }}/{{ $countAvailableProperties }}
            </span>
        @endif
    @endif
</div>