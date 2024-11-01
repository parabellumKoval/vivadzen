{{-- regular object attribute --}}
@php
    $value = data_get($entry, $column['name']);
@endphp

<div class="ak-tag-wrapper">
  @foreach($value as $item)
    <span class="ak-tag" style="background: {{ $item->color }};">
      {{ $item->text }} 
      <button class="ak-tag-remove-btn" data-target="remove-tag-btn" data-id="{{ $item->pivot->id }}">X</button>
    </span>
  @endforeach

  <button
    data-field-related-name="tag"
    data-inline-modal-route="{{ url('/admin/tag/inline/create/modal') }}"
    data-inline-create-route="{{ url('/admin/tag/inline/create/createOrAttach') }}"
    data-inline-modal-class="modal-dialog"
    
    data-parent-loaded-fields="{{json_encode(array_unique(array_column($crud->fields(),'type')))}}"
    data-include-main-form-fields="true"
    data-id="{{ $entry->id }}"
    data-type="{{ $column['data-type'] }}"
    data-target="ak-add-tag-btn"
    class="ak-tag-add-btn"
  >+ Добавить</button>

</div>

