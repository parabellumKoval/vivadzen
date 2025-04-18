<div class="card mb-4">
    <div class="card-body">
        <h4>{{ __('image_generation_settings_widget.settings_title') }}</h4>
        <p>{{ __('image_generation_settings_widget.settings_description') }}</p>

        <form action="/admin/image-generation-history/settings" method="POST">
            @csrf

            <!-- General Settings -->
            <div>
                <div class="custom-control custom-switch">
                    <input name="auto_generation_enabled" type="checkbox" class="custom-control-input" id="autoGenerationEnabled"
                           @if(isset($widget['data']['auto_generation_enabled']) && $widget['data']['auto_generation_enabled']) checked @endif>
                    <label class="custom-control-label" for="autoGenerationEnabled">{{ __('image_generation_settings_widget.auto_generation_enabled') }}</label>
                </div>
                <small class="form-text text-muted mb-2">{{ __('image_generation_settings_widget.auto_generation_enabled_hint') }}</small>

                <div class="custom-control custom-switch mt-2">
                    <input name="use_ai_image_suggestion" type="checkbox" class="custom-control-input" id="useAiImageSuggestion"
                           @if(isset($widget['data']['use_ai_image_suggestion']) && $widget['data']['use_ai_image_suggestion']) checked @endif>
                    <label class="custom-control-label" for="useAiImageSuggestion">{{ __('image_generation_settings_widget.use_ai_image_suggestion') }}</label>
                </div>
                <small class="form-text text-muted">{{ __('image_generation_settings_widget.use_ai_image_suggestion_hint') }}</small>
            </div>

            <hr>

            <div class="row">
                <!-- Products -->
                <div class="col-md-6">
                    <h5 class="mb-3">{{ __('image_generation_settings_widget.products_title') }}</h5>
                    <div class="custom-control custom-switch">
                        <input name="generate_for_products" type="checkbox" class="custom-control-input" id="generateForProducts"
                               @if(isset($widget['data']['generate_for_products']) && $widget['data']['generate_for_products']) checked @endif>
                        <label class="custom-control-label" for="generateForProducts">{{ __('image_generation_settings_widget.generate_for_products') }}</label>
                    </div>
                    <small class="form-text text-muted mb-3">{{ __('image_generation_settings_widget.generate_for_products_hint') }}</small>

                    <hr>

                    <div class="form-check mb-3">
                        <input name="active_products_only" class="form-check-input" type="checkbox" value="1" id="activeProductsOnly"
                               @if(isset($widget['data']['active_products_only']) && $widget['data']['active_products_only']) checked @endif>
                        <label class="form-check-label" for="activeProductsOnly">{{ __('image_generation_settings_widget.active_products_only') }}</label>
                        <small class="form-text text-muted">{{ __('image_generation_settings_widget.active_products_only_hint') }}</small>
                    </div>

                    <div class="form-check mb-3">
                        <input name="in_stock_products_only" class="form-check-input" type="checkbox" value="1" id="inStockProductsOnly"
                               @if(isset($widget['data']['in_stock_products_only']) && $widget['data']['in_stock_products_only']) checked @endif>
                        <label class="form-check-label" for="inStockProductsOnly">{{ __('image_generation_settings_widget.in_stock_products_only') }}</label>
                        <small class="form-text text-muted">{{ __('image_generation_settings_widget.in_stock_products_only_hint') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="minPrice">{{ __('image_generation_settings_widget.min_price') }}</label>
                        <input name="min_price" type="number" class="form-control" id="minPrice"
                               value="{{ isset($widget['data']['min_price']) ? $widget['data']['min_price'] : '' }}">
                        <small class="form-text text-muted">{{ __('image_generation_settings_widget.min_price_hint') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="productImagesCount">{{ __('image_generation_settings_widget.product_images_count') }}</label>
                        <input name="product_images_count" type="number" min="1" class="form-control" id="productImagesCount"
                               value="{{ isset($widget['data']['product_images_count']) ? $widget['data']['product_images_count'] : '1' }}">
                        <small class="form-text text-muted">{{ __('image_generation_settings_widget.product_images_count_hint') }}</small>
                    </div>
                </div>

                <!-- Categories -->
                <div class="col-md-6">
                    <h5 class="mb-3">{{ __('image_generation_settings_widget.categories_title') }}</h5>
                    <div class="custom-control custom-switch">
                        <input name="generate_for_categories" type="checkbox" class="custom-control-input" id="generateForCategories"
                               @if(isset($widget['data']['generate_for_categories']) && $widget['data']['generate_for_categories']) checked @endif>
                        <label class="custom-control-label" for="generateForCategories">{{ __('image_generation_settings_widget.generate_for_categories') }}</label>
                    </div>
                    <small class="form-text text-muted mb-3">{{ __('image_generation_settings_widget.generate_for_categories_hint') }}</small>

                    <hr>

                    <div class="form-check mb-3">
                        <input name="active_categories_only" class="form-check-input" type="checkbox" value="1" id="activeCategoriesOnly"
                               @if(isset($widget['data']['active_categories_only']) && $widget['data']['active_categories_only']) checked @endif>
                        <label class="form-check-label" for="activeCategoriesOnly">{{ __('image_generation_settings_widget.active_categories_only') }}</label>
                        <small class="form-text text-muted">{{ __('image_generation_settings_widget.active_categories_only_hint') }}</small>
                    </div>

                    <div class="form-check mb-3">
                        <input name="categories_with_products_only" class="form-check-input" type="checkbox" value="1" id="categoriesWithProductsOnly"
                               @if(isset($widget['data']['categories_with_products_only']) && $widget['data']['categories_with_products_only']) checked @endif>
                        <label class="form-check-label" for="categoriesWithProductsOnly">{{ __('image_generation_settings_widget.categories_with_products_only') }}</label>
                        <small class="form-text text-muted">{{ __('image_generation_settings_widget.categories_with_products_only_hint') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="categoryImagesCount">{{ __('image_generation_settings_widget.category_images_count') }}</label>
                        <input name="category_images_count" type="number" min="1" class="form-control" id="categoryImagesCount"
                               value="{{ isset($widget['data']['category_images_count']) ? $widget['data']['category_images_count'] : '1' }}">
                        <small class="form-text text-muted">{{ __('image_generation_settings_widget.category_images_count_hint') }}</small>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-6 col-sm-4 col-md-2 col-xl mb-3 mb-xl-0">
                    <button class="btn btn-primary" type="submit">{{ __('image_generation_settings_widget.save_changes') }}</button>
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
        const generateForProducts = $('#generateForProducts');
        const generateForCategories = $('#generateForCategories');
        
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
            
            if (!mainEnabled) {
                generateForProducts.prop('disabled', true);
                generateForCategories.prop('disabled', true);
                productsContainer.addClass('disabled-settings');
                categoriesContainer.addClass('disabled-settings');
            } else {
                generateForProducts.prop('disabled', false);
                generateForCategories.prop('disabled', false);
                updateContainerState(productsContainer, generateForProducts.is(':checked'));
                updateContainerState(categoriesContainer, generateForCategories.is(':checked'));
            }
        }
        
        autoGenerationEnabled.on('change', updateAllStates);
        generateForProducts.on('change', function() {
            updateContainerState(productsContainer, $(this).is(':checked'));
        });
        generateForCategories.on('change', function() {
            updateContainerState(categoriesContainer, $(this).is(':checked'));
        });
        
        // Initial state
        updateAllStates();
    });
</script>
@endpush