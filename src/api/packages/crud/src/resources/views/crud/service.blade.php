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

        <div class="card mb-4">
            <div class="card-header font-weight-bold">
                {{ __('Карточка товара') }}
            </div>
            <div class="card-body">
                <div class="service-entry-preview">
                    @include($serviceEntryCardView, ['entry' => $entry, 'crud' => $crud])
                </div>
            </div>
        </div>

        @include('crud::service.operations.merge')

        @if ($serviceSimilarEnabled ?? false)
            @include('crud::service.operations.similar')
        @endif
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

            const $similarForm = $('#service-similar-form');
            if ($similarForm.length) {
                const endpoint = $similarForm.data('endpoint');
                const $results = $('#service-similar-results');
                const $submit = $('#service-similar-submit');
                const $strictness = $('#service-similar-strictness');
                const initialEmptyText = $results.data('emptyText') || '{{ __('Нет данных для отображения.') }}';

                function setSimilarLoading(state) {
                    $submit.prop('disabled', state);
                    $submit.find('.service-similar-submit-text').toggleClass('d-none', state);
                    $submit.find('.service-similar-submit-spinner').toggleClass('d-none', !state);
                }

                function updateStrictnessDescription() {
                    const $selected = $strictness.find('option:selected');
                    const description = $selected.data('description') || '';
                    $('#service-similar-strictness-description').text(description);
                }

                function renderSimilarResults(response) {
                    const items = (response && response.results) ? response.results : [];

                    if (!items.length) {
                        const message = response && response.meta && response.meta.total === 0
                            ? '{{ __('Совпадений не найдено.') }}'
                            : initialEmptyText;
                        $results
                            .addClass('text-muted')
                            .html('<div class="py-3">'+ message +'</div>');

                        return;
                    }

                    const html = items.map(function(item){
                        return '<div class="col-md-6 mb-3"><div class="service-similar-result h-100">'+ item.html +'</div></div>';
                    }).join('');

                    $results
                        .removeClass('text-muted')
                        .html('<div class="row">'+ html +'</div>');
                }

                function showSimilarError(message) {
                    $results
                        .removeClass('text-muted')
                        .html('<div class="alert alert-danger mb-0">'+ message +'</div>');
                }

                $similarForm.on('submit', function (event) {
                    event.preventDefault();

                    if (!endpoint) {
                        return;
                    }

                    setSimilarLoading(true);
                    $.ajax({
                        type: 'POST',
                        url: endpoint,
                        data: $similarForm.serialize(),
                        success: function (response) {
                            renderSimilarResults(response);
                        },
                        error: function () {
                            showSimilarError('{{ __('Не удалось выполнить поиск. Повторите попытку позже.') }}');
                        },
                        complete: function () {
                            setSimilarLoading(false);
                        }
                    });
                });

                updateStrictnessDescription();
            }
        })(jQuery);
    </script>
@endpush
