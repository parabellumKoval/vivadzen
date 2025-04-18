@php
    $extras = $data ?? [];
@endphp

<div class="card mb-4">
    <div class="card-body">
        <h4>{{ __('ai_generation_settings_widget.settings_title') }}</h4>
        <p>{{ __('ai_generation_settings_widget.settings_description') }}</p>

        <form action="/admin/ai-generation-history/settings" method="POST">
            @csrf

            <!-- General Settings -->
            <div>
                <div class="custom-control custom-switch">
                    <input name="auto_generation_enabled" type="checkbox" class="custom-control-input" id="autoGenerationEnabled"
                           @if(isset($widget['data']['auto_generation_enabled']) && $widget['data']['auto_generation_enabled']) checked @endif>
                    <label class="custom-control-label" for="autoGenerationEnabled">{{ __('ai_generation_settings_widget.auto_generation_enabled') }}</label>
                </div>
                <small class="form-text text-muted mb-2">{{ __('ai_generation_settings_widget.auto_generation_enabled_hint') }}</small>
            </div>

            <hr>

            <div class="row">
                <!-- Products -->
                <div class="col-md-6">
                    <h5 class="mb-3">{{ __('ai_generation_settings_widget.products_title') }}</h5>
                    <div class="custom-control custom-switch">
                        <input name="generate_description" type="checkbox" class="custom-control-input" id="generateDescription"
                               @if(isset($widget['data']['generate_description']) && $widget['data']['generate_description']) checked @endif>
                        <label class="custom-control-label" for="generateDescription">{{ __('ai_generation_settings_widget.generate_product_description') }}</label>
                        <small class="form-text text-muted mb-3">{{ __('ai_generation_settings_widget.generate_description_hint') }}</small>
                    </div>

                    <div class="custom-control custom-switch mb-3">
                        <input name="detect_brand" type="checkbox" class="custom-control-input" id="detectBrand"
                               @if(isset($widget['data']['detect_brand']) && $widget['data']['detect_brand']) checked @endif>
                        <label class="custom-control-label" for="detectBrand">{{ __('ai_generation_settings_widget.detect_brand') }}</label>
                        <small class="form-text text-muted">{{ __('ai_generation_settings_widget.detect_brand_hint') }}</small>
                    </div>

                    <div class="custom-control custom-switch mb-3">
                        <input name="detect_category" type="checkbox" class="custom-control-input" id="detectCategory"
                               @if(isset($widget['data']['detect_category']) && $widget['data']['detect_category']) checked @endif>
                        <label class="custom-control-label" for="detectCategory">{{ __('ai_generation_settings_widget.detect_category') }}</label>
                        <small class="form-text text-muted">{{ __('ai_generation_settings_widget.detect_category_hint') }}</small>
                    </div>

                    <div class="custom-control custom-switch mb-3">
                        <input name="fill_characteristics" type="checkbox" class="custom-control-input" id="fillCharacteristics"
                               @if(isset($widget['data']['fill_characteristics']) && $widget['data']['fill_characteristics']) checked @endif>
                        <label class="custom-control-label" for="fillCharacteristics">{{ __('ai_generation_settings_widget.fill_characteristics') }}</label>
                        <small class="form-text text-muted">{{ __('ai_generation_settings_widget.fill_characteristics_hint') }}</small>
                    </div>
                    <hr>

                    <div class="form-check mb-3">
                        <input name="active_products_only" class="form-check-input" type="checkbox" value="1" id="activeProductsOnly"
                               @if(isset($widget['data']['active_products_only']) && $widget['data']['active_products_only']) checked @endif>
                        <label class="form-check-label" for="activeProductsOnly">{{ __('ai_generation_settings_widget.active_products_only') }}</label>
                        <small class="form-text text-muted">{{ __('ai_generation_settings_widget.active_products_only_hint') }}</small>
                    </div>

                    <div class="form-check mb-3">
                        <input name="in_stock_products_only" class="form-check-input" type="checkbox" value="1" id="inStockProductsOnly"
                               @if(isset($widget['data']['in_stock_products_only']) && $widget['data']['in_stock_products_only']) checked @endif>
                        <label class="form-check-label" for="inStockProductsOnly">{{ __('ai_generation_settings_widget.in_stock_products_only') }}</label>
                        <small class="form-text text-muted">{{ __('ai_generation_settings_widget.in_stock_products_only_hint') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="minPrice">{{ __('ai_generation_settings_widget.min_price') }}</label>
                        <input name="min_price" type="number" class="form-control" id="minPrice"
                               value="{{ isset($widget['data']['min_price']) ? $widget['data']['min_price'] : '' }}">
                        <small class="form-text text-muted">{{ __('ai_generation_settings_widget.min_price_hint') }}</small>
                    </div>
                    
                </div>

                <!-- Categories -->
                <div class="col-md-6">
                    <h5 class="mb-3">{{ __('ai_generation_settings_widget.categories_title') }}</h5>
                    <div class="custom-control custom-switch">
                        <input name="generate_for_categories" type="checkbox" class="custom-control-input" id="generateForCategories"
                               @if(isset($widget['data']['generate_for_categories']) && $widget['data']['generate_for_categories']) checked @endif>
                        <label class="custom-control-label" for="generateForCategories">{{ __('ai_generation_settings_widget.generate_for_categories') }}</label>
                    </div>
                    <small class="form-text text-muted mb-3">{{ __('ai_generation_settings_widget.generate_for_categories_hint') }}</small>

                    <hr>

                    <div class="form-check mb-3">
                        <input name="active_categories_only" class="form-check-input" type="checkbox" value="1" id="activeCategoriesOnly"
                               @if(isset($widget['data']['active_categories_only']) && $widget['data']['active_categories_only']) checked @endif>
                        <label class="form-check-label" for="activeCategoriesOnly">{{ __('ai_generation_settings_widget.active_categories_only') }}</label>
                        <small class="form-text text-muted">{{ __('ai_generation_settings_widget.active_categories_only_hint') }}</small>
                    </div>

                    <div class="form-check mb-3">
                        <input name="categories_with_products_only" class="form-check-input" type="checkbox" value="1" id="categoriesWithProductsOnly"
                               @if(isset($widget['data']['categories_with_products_only']) && $widget['data']['categories_with_products_only']) checked @endif>
                        <label class="form-check-label" for="categoriesWithProductsOnly">{{ __('ai_generation_settings_widget.categories_with_products_only') }}</label>
                        <small class="form-text text-muted">{{ __('ai_generation_settings_widget.categories_with_products_only_hint') }}</small>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-6 col-sm-4 col-md-2 col-xl mb-3 mb-xl-0">
                    <button class="btn btn-primary" type="submit">{{ __('ai_generation_settings_widget.save_changes') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('after_styles')
<style>
    .disabled-settings {
        opacity: 0.8;
        pointer-events: none;
    }
    .disabled-settings input,
    .disabled-settings select,
    .disabled-settings button {
        cursor: default;
    }
</style>
@endpush

@push('after_scripts')
<script>
    $(document).ready(function() {
        const autoGenerationEnabled = $('#autoGenerationEnabled');
        const generateForProducts = $('#generateDescription');
        const generateForCategories = $('#generateForCategories');
        
        // Product generation checkboxes
        const detectBrand = $('#detectBrand');
        const detectCategory = $('#detectCategory');
        const fillCharacteristics = $('#fillCharacteristics');
        
        // Product filter inputs
        const activeProductsOnly = $('#activeProductsOnly');
        const inStockProductsOnly = $('#inStockProductsOnly');
        const minPrice = $('#minPrice');

        // Category filter inputs
        const activeCategoriesOnly = $('#activeCategoriesOnly');
        const categoriesWithProductsOnly = $('#categoriesWithProductsOnly');
        
        const productsContainer = generateForProducts.closest('.col-md-6');
        const categoriesContainer = generateForCategories.closest('.col-md-6');
        
        function updateContainerState(container, enabled) {
            container.find('input, select').not('[type="checkbox"]:first').prop('disabled', !enabled);
            if (enabled) {
                container.removeClass('disabled-settings');
            } else {
                container.addClass('disabled-settings');
            }
        }

        function updateAllStates() {
            const mainEnabled = autoGenerationEnabled.is(':checked');
            
            // Control all inputs in the form except auto_generation_enabled itself
            const allOtherInputs = $('input:not(#autoGenerationEnabled), select');
            allOtherInputs.prop('disabled', !mainEnabled);
            
            if (!mainEnabled) {
                // Disable all sections
                productsContainer.addClass('disabled-settings');
                categoriesContainer.addClass('disabled-settings');
            } else {
                productsContainer.removeClass('disabled-settings');
                categoriesContainer.removeClass('disabled-settings');
                // Re-enable the filter inputs based on checkbox states
                updateProductFilterInputs();
                updateCategoryFilterInputs();
            }
        }

        function updateProductFilterInputs() {
            // Only update filter inputs if main switch is enabled
            if (autoGenerationEnabled.is(':checked')) {
                const anyCheckboxEnabled = 
                    generateForProducts.is(':checked') || 
                    detectBrand.is(':checked') || 
                    detectCategory.is(':checked') || 
                    fillCharacteristics.is(':checked');

                activeProductsOnly.prop('disabled', !anyCheckboxEnabled);
                inStockProductsOnly.prop('disabled', !anyCheckboxEnabled);
                minPrice.prop('disabled', !anyCheckboxEnabled);

                // Update visual state
                const filterInputsContainer = activeProductsOnly.closest('.form-check').parent();
                if (!anyCheckboxEnabled) {
                    filterInputsContainer.addClass('disabled-settings');
                } else {
                    filterInputsContainer.removeClass('disabled-settings');
                }
            }
        }

        function updateCategoryFilterInputs() {
            // Only update if main switch is enabled
            if (autoGenerationEnabled.is(':checked')) {
                const generateCategoriesEnabled = generateForCategories.is(':checked');
                
                // Only update disabled state, do not change checked state
                activeCategoriesOnly.prop('disabled', !generateCategoriesEnabled);
                categoriesWithProductsOnly.prop('disabled', !generateCategoriesEnabled);
            }
        }
        
        // Add event listeners
        autoGenerationEnabled.on('change', updateAllStates);
        generateForProducts.on('change', updateProductFilterInputs);
        generateForCategories.on('change', function() {
            if (autoGenerationEnabled.is(':checked')) {
                updateContainerState(categoriesContainer, $(this).is(':checked'));
                updateCategoryFilterInputs();
            }
        });
        
        detectBrand.on('change', updateProductFilterInputs);
        detectCategory.on('change', updateProductFilterInputs);
        fillCharacteristics.on('change', updateProductFilterInputs);
        
        // Initial state
        updateAllStates();
    });
</script>
@endpush