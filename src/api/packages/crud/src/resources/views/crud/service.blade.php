@extends(backpack_view('blank'))

@php
    $defaultBreadcrumbs = [
        trans('backpack::crud.admin') => backpack_url('dashboard'),
        $crud->entity_name_plural => url($crud->route),
        __('Режим обслуживания') => false,
    ];

    $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
    $selectedFields = old('fields', $serviceMerge['default_fields'] ?? []);
    $forcedFields = old('force', collect($serviceMergeFields)->filter(fn ($field) => $field['force'])->pluck('key')->all());
    $deleteSource = old('delete_source', $serviceDeleteDefault ? 1 : 0);
    $serviceRelations = $serviceRelations ?? [];
    $relationDefaults = $serviceRelationsDefault ?? [];
    $selectedRelations = old('relations', $relationDefaults);
    $relationSettings = old('relation_settings', []);
    $strategyLabels = [
        'translations' => __('Переводы'),
        'append' => __('Добавление'),
        'replace' => __('Замена'),
    ];
@endphp

@section('header')
    <section class="container-fluid">
        <h2>
            <span class="text-capitalize">{!! $crud->entity_name_plural !!}</span>
            <small>{{ __('Режим обслуживания записи #:id', ['id' => $entry->getKey()]) }}</small>
        </h2>
    </section>
@endsection

@section('content')
<div class="row">
    <div class="{{ $crud->getServiceContentClass() }}">
        @include('crud::inc.grouped_errors')

        <form method="post" action="{{ url($crud->route.'/'.$entry->getKey().'/service/merge') }}">
            @csrf

            <div class="card mb-4">
                <div class="card-header font-weight-bold">{{ $serviceMerge['label'] ?? __('Слияние записей') }}</div>
                <div class="card-body">
                    <p class="text-muted mb-4">{{ $serviceMerge['description'] ?? '' }}</p>

                    <div class="form-group">
                        <label for="service-merge-target" class="font-weight-bold">
                            {{ __('Запись-приемник') }}
                        </label>
                        <select name="target_entry_id"
                                id="service-merge-target"
                                class="form-control"
                                data-selected="{{ old('target_entry_id') }}"
                                data-source-id="{{ $entry->getKey() }}"
                                data-endpoint="{{ $serviceCandidatesEndpoint }}"
                                required></select>
                        @if ($errors->has('target_entry_id'))
                            <span class="text-danger small">{{ $errors->first('target_entry_id') }}</span>
                        @endif
                    </div>

                    <div class="form-group form-check">
                        <input type="hidden" name="delete_source" value="0">
                        <input type="checkbox"
                               class="form-check-input"
                               id="service-delete-source"
                               name="delete_source"
                               value="1"
                               {{ $deleteSource ? 'checked' : '' }}>
                        <label class="form-check-label" for="service-delete-source">
                            {{ __('Удалить текущую запись после слияния') }}
                        </label>
                    </div>

                    @if ($serviceRelations !== [])
                        <hr>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold d-block mb-2">{{ __('Связи для слияния') }}</label>
                            <div class="list-group">
                                @foreach ($serviceRelations as $relation)
                                    @php
                                        $relationKey = $relation['key'];
                                        $relationChecked = in_array($relationKey, (array) $selectedRelations, true);
                                        $mergeConfig = $relation['merge'] ?? null;
                                        $mergeOptions = data_get($relationSettings, $relationKey, []);
                                        $mergeInput = data_get($mergeOptions, 'merge', []);
                                        $mergeEnabled = false;
                                        $mergeMode = $mergeConfig['default_mode'] ?? null;

                                        if ($mergeConfig) {
                                            $mergeEnabledRaw = data_get($mergeInput, 'enabled');
                                            $mergeEnabled = $mergeEnabledRaw === null
                                                ? (bool) ($mergeConfig['default'] ?? false)
                                                : filter_var($mergeEnabledRaw, FILTER_VALIDATE_BOOLEAN);
                                            $mergeMode = data_get($mergeInput, 'mode') ?? ($mergeConfig['default_mode'] ?? null);
                                        }
                                    @endphp
                                    <div class="list-group-item" data-relation-wrapper="{{ $relationKey }}">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox"
                                                   class="custom-control-input js-relation-toggle"
                                                   data-relation="{{ $relationKey }}"
                                                   id="merge-relation-{{ $relationKey }}"
                                                   name="relations[]"
                                                   value="{{ $relationKey }}"
                                                   {{ $relationChecked ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="merge-relation-{{ $relationKey }}">
                                                {{ $relation['label'] }}
                                            </label>
                                        </div>
                                        @if ($relation['help'])
                                            <p class="text-muted small mb-0 mt-2">{{ $relation['help'] }}</p>
                                        @endif

                                        @if ($mergeConfig && !empty($mergeConfig['modes']))
                                            <div class="mt-3 pl-4 border-left js-relation-options" data-relation-options="{{ $relationKey }}">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="hidden"
                                                           name="relation_settings[{{ $relationKey }}][merge][enabled]"
                                                           value="0">
                                                    <input type="checkbox"
                                                           class="custom-control-input"
                                                           id="merge-relation-{{ $relationKey }}-duplicates"
                                                           name="relation_settings[{{ $relationKey }}][merge][enabled]"
                                                           value="1"
                                                           {{ $mergeEnabled ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="merge-relation-{{ $relationKey }}-duplicates">
                                                        {{ $mergeConfig['label'] ?? __('Сшивать найденные дубликаты') }}
                                                    </label>
                                                </div>

                                                <div class="form-group mt-3 mb-2">
                                                    <label for="merge-relation-{{ $relationKey }}-mode" class="small font-weight-bold text-muted mb-1">
                                                        {{ __('Находить похожие по') }}
                                                    </label>
                                                    <select class="form-control form-control-sm js-relation-mode"
                                                            id="merge-relation-{{ $relationKey }}-mode"
                                                            name="relation_settings[{{ $relationKey }}][merge][mode]"
                                                            data-relation="{{ $relationKey }}">
                                                        @foreach ($mergeConfig['modes'] as $mode)
                                                            <option value="{{ $mode['key'] }}" {{ ($mergeMode === $mode['key']) ? 'selected' : '' }}>
                                                                {{ $mode['label'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @foreach ($mergeConfig['modes'] as $mode)
                                                        @if (!empty($mode['description']))
                                                            <small class="text-muted d-block mt-2 {{ $mergeMode === $mode['key'] ? '' : 'd-none' }}"
                                                                   data-merge-mode-help="{{ $relationKey }}"
                                                                   data-merge-mode="{{ $mode['key'] }}">
                                                                {{ $mode['description'] }}
                                                            </small>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header font-weight-bold">{{ __('Поля для слияния') }}</div>
                <div class="card-body">
                    @if ($serviceMergeFields === [])
                        <p class="text-muted mb-0">{{ __('Для этой модели не настроены поля слияния.') }}</p>
                    @else
                        <div class="list-group">
                            @foreach ($serviceMergeFields as $field)
                                @php
                                    $isSelected = in_array($field['key'], $selectedFields, true);
                                    $isForce = in_array($field['key'], $forcedFields, true);
                                @endphp
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       class="custom-control-input js-merge-field"
                                                       id="merge-field-{{ $field['key'] }}"
                                                       name="fields[]"
                                                       value="{{ $field['key'] }}"
                                                       {{ $isSelected ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold" for="merge-field-{{ $field['key'] }}">
                                                    {{ $field['label'] }}
                                                </label>
                                                <span class="badge badge-light ml-2">{{ $strategyLabels[$field['strategy']] ?? \Illuminate\Support\Str::title($field['strategy']) }}</span>
                                            </div>
                                            @if ($field['help'])
                                                <p class="text-muted mb-0 small mt-2">{{ $field['help'] }}</p>
                                            @endif
                                        </div>

                                        @if ($field['forceable'])
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox"
                                                       class="custom-control-input js-merge-force"
                                                       id="merge-force-{{ $field['key'] }}"
                                                       name="force[]"
                                                       value="{{ $field['key'] }}"
                                                       {{ $isForce ? 'checked' : '' }}
                                                       {{ $isSelected ? '' : 'disabled' }}>
                                                <label class="custom-control-label" for="merge-force-{{ $field['key'] }}">
                                                    {{ __('Force') }}
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @if ($errors->has('service_merge'))
                <div class="alert alert-danger">
                    {{ $errors->first('service_merge') }}
                </div>
            @endif

            <div class="text-right">
                <button class="btn btn-primary" type="submit" {{ $serviceMergeFields === [] ? 'disabled' : '' }}>
                    {{ __('Выполнить слияние') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('after_styles')
    <link href="{{ asset('packages/select2/dist/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet">
@endpush

@push('after_scripts')
    <script src="{{ asset('packages/select2/dist/js/select2.full.min.js') }}"></script>
    @if (app()->getLocale() !== 'en')
        <script src="{{ asset('packages/select2/dist/js/i18n/' . str_replace('_', '-', app()->getLocale()) . '.js') }}"></script>
    @endif
    <script>
        (function($){
            const $target = $('#service-merge-target');
            const endpoint = $target.data('endpoint');
            const sourceId = $target.data('source-id');
            const selected = $target.data('selected');

            $target.select2({
                theme: 'bootstrap',
                placeholder: '{{ __('Выберите запись для слияния') }}',
                ajax: {
                    url: endpoint,
                    dataType: 'json',
                    delay: 200,
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page || 1,
                            source_id: sourceId,
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results || []
                        };
                    }
                },
                allowClear: true,
            });

            if (selected) {
                $.get(endpoint, { selected: selected }, function(response) {
                    if (response && response.results && response.results.length) {
                        const entry = response.results[0];
                        const option = new Option(entry.text, entry.id, true, true);
                        $target.append(option).trigger('change');
                    }
                });
            }

            $('.js-merge-field').on('change', function(){
                const fieldKey = $(this).val();
                const checked = $(this).is(':checked');
                $("#merge-force-" + fieldKey).prop('disabled', !checked);
            });

            function toggleRelationOptions($checkbox) {
                const relation = $checkbox.data('relation');
                const enabled = $checkbox.is(':checked');
                const $container = $('[data-relation-options="' + relation + '"]');

                if (!$container.length) {
                    return;
                }

                $container.toggleClass('text-muted', !enabled);
                $container.find('input:not([type="hidden"]), select, textarea').prop('disabled', !enabled);
            }

            function updateRelationModeHelp($select) {
                const relation = $select.data('relation');
                const mode = $select.val();

                $('[data-merge-mode-help="' + relation + '"]').addClass('d-none');
                $('[data-merge-mode-help="' + relation + '"][data-merge-mode="' + mode + '"]').removeClass('d-none');
            }

            $('.js-relation-toggle').each(function(){
                toggleRelationOptions($(this));
            }).on('change', function(){
                toggleRelationOptions($(this));
            });

            $('.js-relation-mode').each(function(){
                updateRelationModeHelp($(this));
            }).on('change', function(){
                updateRelationModeHelp($(this));
            });
        })(jQuery);
    </script>
@endpush
