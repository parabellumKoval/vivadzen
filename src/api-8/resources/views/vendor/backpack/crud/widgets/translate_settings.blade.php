<div class="card mb-4">
    <div class="card-body">
        <h4>{{ __('translate_settings_widget.settings_title') }}</h4>
        <p>{{ __('translate_settings_widget.settings_description') }}</p>

        <form action="/admin/translation-history/settings" method="POST">
          @csrf

          <div>
            <div class="custom-control custom-switch">
              <input name="auto_translate_enabled" type="checkbox" class="custom-control-input" id="autoTranslateEnabled" 
                     @if(isset($widget['data']['auto_translate_enabled']) && $widget['data']['auto_translate_enabled']) checked @endif>
              <label class="custom-control-label" for="autoTranslateEnabled">{{ __('translate_settings_widget.auto_translate_enabled') }}</label>
            </div>
          </div>

          <hr>

          <div class="row">
            <!-- Товары -->
            <div class="col-md-4">
              <h5 class="mb-3">{{ __('translate_settings_widget.products_title') }}</h5>
              <div class="custom-control custom-switch">
                <input name="translate_products" type="checkbox" class="custom-control-input" id="translateProducts"
                       @if(isset($widget['data']['translate_products']) && $widget['data']['translate_products']) checked @endif>
                <label class="custom-control-label" for="translateProducts">{{ __('translate_settings_widget.translate_products') }}</label>
              </div>

              <hr>

              <div class="form-check mb-3">
                <input name="active_products_only" class="form-check-input" type="checkbox" value="1" id="activeProductsOnly"
                       @if(isset($widget['data']['active_products_only']) && $widget['data']['active_products_only']) checked @endif>
                <label class="form-check-label" for="activeProductsOnly">{{ __('translate_settings_widget.active_products_only') }}</label>
                <small class="form-text text-muted">{{ __('translate_settings_widget.active_products_only_hint') }}</small>
              </div>

              <div class="form-check mb-3">
                <input name="in_stock_products_only" class="form-check-input" type="checkbox" value="1" id="inStockProductsOnly"
                       @if(isset($widget['data']['in_stock_products_only']) && $widget['data']['in_stock_products_only']) checked @endif>
                <label class="form-check-label" for="inStockProductsOnly">{{ __('translate_settings_widget.in_stock_products_only') }}</label>
                <small class="form-text text-muted">{{ __('translate_settings_widget.in_stock_products_only_hint') }}</small>
              </div>

              <div class="form-group">
                <label for="minPrice">{{ __('translate_settings_widget.min_price') }}</label>
                <input name="min_price" type="number" class="form-control" id="minPrice" 
                       value="{{ isset($widget['data']['min_price']) ? $widget['data']['min_price'] : '' }}">
                <small class="form-text text-muted">{{ __('translate_settings_widget.min_price_hint') }}</small>
              </div>

              <div class="form-group">
                <label for="minPrice">{{ __('translate_settings_widget.min_symbols') }}</label>
                <input name="min_symbols" type="number" class="form-control" id="minPrice" 
                       value="{{ isset($widget['data']['min_symbols']) ? $widget['data']['min_symbols'] : '' }}">
                <small class="form-text text-muted">{{ __('translate_settings_widget.min_symbols_hint') }}</small>
              </div>
            </div>

            <!-- Характеристики -->
            <div class="col-md-4">
              <h5 class="mb-3">{{ __('translate_settings_widget.specs_title') }}</h5>
              <div class="custom-control custom-switch">
                <input name="translate_specs" type="checkbox" class="custom-control-input" id="translateSpecs"
                       @if(isset($widget['data']['translate_specs']) && $widget['data']['translate_specs']) checked @endif>
                <label class="custom-control-label" for="translateSpecs">{{ __('translate_settings_widget.translate_specs') }}</label>
              </div>
              <hr>

              <div class="form-check mb-3">
                <input name="translate_attribute_names" class="form-check-input" type="checkbox" value="1" id="translateAttributeNames"
                     @if(isset($widget['data']['translate_attribute_names']) && $widget['data']['translate_attribute_names']) checked @endif>
                <label class="form-check-label" for="translateAttributeNames">{{ __('translate_settings_widget.translate_attribute_names') }}</label>
                <small class="form-text text-muted">{{ __('translate_settings_widget.translate_attribute_names_hint') }}</small>
              </div>

              <div class="form-check mb-3">
                <input name="translate_attribute_values" class="form-check-input" type="checkbox" value="1" id="translateAttributeValues"
                   @if(isset($widget['data']['translate_attribute_values']) && $widget['data']['translate_attribute_values']) checked @endif>
                <label class="form-check-label" for="translateAttributeValues">{{ __('translate_settings_widget.translate_attribute_values') }}</label>
                <small class="form-text text-muted">{{ __('translate_settings_widget.translate_attribute_values_hint') }}</small>
              </div>

              <div class="form-check mb-3">
                <input name="translate_product_attribute_values" class="form-check-input" type="checkbox" value="1" id="translateProductAttributeValues"
                  @if(isset($widget['data']['translate_product_attribute_values']) && $widget['data']['translate_product_attribute_values']) checked @endif>
                <label class="form-check-label" for="translateProductAttributeValues">{{ __('translate_settings_widget.translate_product_attribute_values') }}</label>
                <small class="form-text text-muted">{{ __('translate_settings_widget.translate_product_attribute_values_hint') }}</small>
              </div>

              <div class="form-check mb-3">
                <input name="unique_product_specs" class="form-check-input" type="checkbox" value="1" id="uniqueProductSpecs"
                  @if(isset($widget['data']['unique_product_specs']) && $widget['data']['unique_product_specs']) checked @endif>
                <label class="form-check-label" for="uniqueProductSpecs">{{ __('translate_settings_widget.unique_product_specs') }}</label>
                <small class="form-text text-muted">{{ __('translate_settings_widget.unique_product_specs_hint') }}</small>
              </div>
            </div>

            <!-- Бренды -->
            <div class="col-md-4">
              <h5 class="mb-3">Бренды</h5>
              <div class="custom-control custom-switch">
                <input name="translate_brands" type="checkbox" class="custom-control-input" id="translateBrands"
                        @if(isset($widget['data']['translate_brands']) && $widget['data']['translate_brands']) checked @endif>
                <label class="custom-control-label" for="translateBrands">{{ __('translate_settings_widget.translate_brands') }}</label>
              </div>
              <hr>

              <div class="form-check mb-3">
                <input name="active_brands_only" class="form-check-input" type="checkbox" value="1" id="activeBrandsOnly"
                     @if(isset($widget['data']['active_brands_only']) && $widget['data']['active_brands_only']) checked @endif>
                <label class="form-check-label" for="activeBrandsOnly">{{ __('translate_settings_widget.active_brands_only') }}</label>
                <small class="form-text text-muted">{{ __('translate_settings_widget.active_brands_only_hint') }}</small>
              </div>
            </div>

          </div> <!-- row -->
          

          <div class="row">
            <div class="col-6 col-sm-4 col-md-2 col-xl mb-3 mb-xl-0">
              <button class="btn btn-primary" type="submit">{{ __('translate_settings_widget.save_changes') }}</button>
            </div>
          </div>
        </form>

    </div>
</div>

@push('after_styles')
<style>
  .disabled-settings {
    opacity: 0.8;
    pointer-events: none; /* Prevents clicks */
  }
  .disabled-settings input,
  .disabled-settings select,
  .disabled-settings button {
    cursor: default; /* Changes cursor to default */
  }
</style>
@endpush

@push('after_scripts')
<script>
  $(document).ready(function() {
    const autoTranslateEnabled = $('#autoTranslateEnabled');
    const settingsToDisable = $('input[name="translate_products"], input[name="active_products_only"], input[name="in_stock_products_only"], input[name="min_price"], input[name="min_symbols"], input[name="translate_specs"], input[name="translate_attribute_names"], input[name="translate_attribute_values"], input[name="translate_product_attribute_values"], input[name="unique_product_specs"], input[name="translate_brands"], input[name="active_brands_only"]');

    const translateProductsSwitch = $('#translateProducts');
    const translateSpecsSwitch = $('#translateSpecs');
    const translateBrandsSwitch = $('#translateBrands');

    const translateProductsContainer = translateProductsSwitch.closest('.col-md-4');
    const translateSpecsContainer = translateSpecsSwitch.closest('.col-md-4');
    const translateBrandsContainer = translateBrandsSwitch.closest('.col-md-4');

    function updateSettingsState() {
      if (autoTranslateEnabled.is(':checked')) {
        settingsToDisable.prop('disabled', false).removeClass('disabled-settings');
      } else {
        settingsToDisable.prop('disabled', true).addClass('disabled-settings');
      }
    }

    function toggleContainerFields(container, isEnabled) {
      container.find('input, select, button').not('[name="' + container.find('input[type="checkbox"]').attr('name') + '"]').prop('disabled', !isEnabled);
      if (isEnabled) {
        container.removeClass('disabled-settings');
      } else {
        container.addClass('disabled-settings');
      }
    }

    function updateContainerStates() {
      toggleContainerFields(translateProductsContainer, translateProductsSwitch.is(':checked'));
      toggleContainerFields(translateSpecsContainer, translateSpecsSwitch.is(':checked'));
      toggleContainerFields(translateBrandsContainer, translateBrandsSwitch.is(':checked'));
    }

    autoTranslateEnabled.on('change', function() {
      updateSettingsState();
      updateContainerStates();
    });

    translateProductsSwitch.on('change', function() {
      toggleContainerFields(translateProductsContainer, $(this).is(':checked'));
    });

    translateSpecsSwitch.on('change', function() {
      toggleContainerFields(translateSpecsContainer, $(this).is(':checked'));
    });

    translateBrandsSwitch.on('change', function() {
      toggleContainerFields(translateBrandsContainer, $(this).is(':checked'));
    });

    // Initial state on page load
    updateSettingsState();
    updateContainerStates();
  });
</script>
@endpush
