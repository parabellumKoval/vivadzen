<div class="card mb-4">
    <div class="card-header font-weight-bold d-flex align-items-center justify-content-between">
        <div>{{ $serviceSimilar['label'] ?? __('Поиск похожих записей') }}</div>
        <span class="badge badge-light">{{ __('До :limit записей', ['limit' => $serviceSimilar['limit'] ?? 20]) }}</span>
    </div>
    <div class="card-body">
        <p class="text-muted mb-4">{{ $serviceSimilar['description'] ?? __('Запускает поиск возможных дублей по данным текущей записи.') }}</p>

        <form id="service-similar-form"
              data-endpoint="{{ $serviceSimilarEndpoint }}"
              class="mb-4">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="service-similar-strictness" class="font-weight-bold">
                        {{ __('Жёсткость поиска') }}
                    </label>
                    @php
                        $strictnessOptions = data_get($serviceSimilar, 'strictness.options', []);
                        $strictnessDefault = $serviceSimilarDefaultStrictness ?? data_get($serviceSimilar, 'strictness.default');
                    @endphp
                    <select class="form-control"
                            id="service-similar-strictness"
                            name="strictness"
                            data-description-target="#service-similar-strictness-description">
                        @foreach ($strictnessOptions as $key => $option)
                            <option value="{{ $key }}" data-description="{{ $option['description'] ?? '' }}" {{ $strictnessDefault === $key ? 'selected' : '' }}>
                                {{ $option['label'] ?? $key }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-2" id="service-similar-strictness-description">
                        {{ data_get($strictnessOptions, $strictnessDefault.'.description') }}
                    </small>
                </div>

                @php
                    $excludeConfig = $serviceSimilar['exclude_children'] ?? [];
                @endphp
                @if ($excludeConfig['enabled'] ?? false)
                    <div class="form-group col-md-4 align-self-end">
                        <div class="custom-control custom-checkbox">
                            <input type="hidden" name="exclude_children" value="0">
                            <input type="checkbox"
                                   class="custom-control-input"
                                   id="service-similar-exclude"
                                   name="exclude_children"
                                   value="1"
                                   {{ ($excludeConfig['default'] ?? true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="service-similar-exclude">
                                {{ __('Исключить дочерние записи') }}
                            </label>
                        </div>
                        <small class="text-muted d-block mt-2" id="service-similar-strictness-description">
                            {{ __('Исключить модификации этого же товара') }}
                        </small>
                    </div>
                @endif

                <div class="form-group col-md-4 text-md-right align-self-end">
                    <button type="submit" class="btn btn-outline-primary" id="service-similar-submit">
                        <span class="service-similar-submit-text">{{ __('Найти похожие') }}</span>
                        <span class="service-similar-submit-spinner d-none">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ __('Поиск...') }}
                        </span>
                    </button>
                </div>
            </div>
        </form>

        <div id="service-similar-results" class="service-similar-results border rounded p-3 text-muted" data-empty-text="{{ __('Заполните форму и запустите поиск.') }}">
            {{ __('Заполните форму и запустите поиск.') }}
        </div>
    </div>
</div>