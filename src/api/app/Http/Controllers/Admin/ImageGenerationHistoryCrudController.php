<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Backpack\Settings\app\Models\Settings;

use App\Models\ImageGenerationHistory;
/**
 * Class ImageGenerationHistoryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class ImageGenerationHistoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;
    


    public function setup()
    {
        $this->crud->setModel(ImageGenerationHistory::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/image-generation-history');
        $this->crud->setEntityNameStrings(
            trans('image_generation_history.entity_name.singular'),
            trans('image_generation_history.entity_name.plural')
        );
    }

    protected function setupListOperation()
    {
        $settings = Settings::where('key', 'image_generation_settings')->first();

        // Add widget for image generation settings
        $widget_definition_array = [
            'type'     => 'view',
            'view'     => 'crud::widgets.image_generation_settings',
            'position' => 'before_content',
            'data'     => $settings->extras ?? null,
        ];

        Widget::add($widget_definition_array);

        $this->crud->addFilter([
            'name' => 'generatable_type',
            'label' => trans('image_generation_history.filters.type'),
            'type' => 'select2',
        ], function () {
            return ImageGenerationHistory::TYPES;
        }, function ($type) {
            $this->crud->query->where('generatable_type', $type);
        });

        $this->crud->addFilter([
            'name' => 'status',
            'label' => trans('image_generation_history.filters.status'),
            'type' => 'select2',
        ], function () {
            return __('status.common');
        }, function ($status) {
            $this->crud->query->where('status', $status);
        });

        $this->crud->addColumn([
            'name' => 'generatable_type',
            'label' => trans('image_generation_history.columns.generatable_type'),
            'type' => 'select_from_array',
            'options' => ImageGenerationHistory::TYPES
        ]);

        $this->crud->addColumn([
            'name' => 'generatableLinkAdmin',
            'label' => trans('image_generation_history.columns.generatable'),
            'type' => 'textarea',
            'limit' => 300,
            'priority' => 1,
        ]);

        $this->crud->addColumn([
            'name' => 'statusHtml',
            'label' => trans('image_generation_history.columns.status'),
            'limit' => 1200,
            'escaped' => false
        ]);

        $this->crud->addColumn([
            'name' => 'messageAdmin',
            'label' => trans('image_generation_history.columns.message'),
            'limit' => 300,
        ]);

        $this->crud->addColumn([
            'name' => 'updated_at',
            'label' => trans('image_generation_history.columns.updated_at'),
        ]);
    }
        
    /**
     * Save image generation settings
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveImageGenerationSettings(Request $request) {
        $settings = Settings::firstOrNew(['key' => 'image_generation_settings']);
        
        $currentExtras = $settings->extras ?? [];
        
        $autoGenEnabled = $request->has('auto_generation_enabled') 
            ? (bool)$request->input('auto_generation_enabled') 
            : false;
        
        $generateForProducts = $request->has('generate_for_products') 
            ? (bool)$request->input('generate_for_products') 
            : ($autoGenEnabled? false: ($currentExtras['generate_for_products'] ?? false));
        
        $generateForCategories = $request->has('generate_for_categories') 
            ? (bool)$request->input('generate_for_categories') 
            : ($autoGenEnabled? false: ($currentExtras['generate_for_categories'] ?? false));
        
        $newExtrasData = [
            // General
            'auto_generation_enabled' => $autoGenEnabled,
            'use_ai_image_suggestion' => $autoGenEnabled
                ? ($request->has('use_ai_image_suggestion') ? (bool)$request->input('use_ai_image_suggestion') : false)
                : ($currentExtras['use_ai_image_suggestion'] ?? false),
        
            // Products
            'generate_for_products' => $generateForProducts,
            'active_products_only' => ($autoGenEnabled && $generateForProducts)
                ? ($request->has('active_products_only') ? (bool)$request->input('active_products_only') : false)
                : ($currentExtras['active_products_only'] ?? false),
            'in_stock_products_only' => ($autoGenEnabled && $generateForProducts)
                ? ($request->has('in_stock_products_only') ? (bool)$request->input('in_stock_products_only') : false)
                : ($currentExtras['in_stock_products_only'] ?? false),
            'min_price' => ($autoGenEnabled && $generateForProducts)
                ? ($request->has('min_price') ? (int)$request->input('min_price') : 0)
                : ($currentExtras['min_price'] ?? 0),
            'product_images_count' => ($autoGenEnabled && $generateForProducts)
                ? ($request->has('product_images_count') ? (int)$request->input('product_images_count') : 1)
                : ($currentExtras['product_images_count'] ?? 1),
        
            // Categories
            'generate_for_categories' => $generateForCategories,
            'active_categories_only' => ($autoGenEnabled && $generateForCategories)
                ? ($request->has('active_categories_only') ? (bool)$request->input('active_categories_only') : false)
                : ($currentExtras['active_categories_only'] ?? false),
            'categories_with_products_only' => ($autoGenEnabled && $generateForCategories)
                ? ($request->has('categories_with_products_only') ? (bool)$request->input('categories_with_products_only') : false)
                : ($currentExtras['categories_with_products_only'] ?? false),
            'category_images_count' => ($autoGenEnabled && $generateForCategories)
                ? ($request->has('category_images_count') ? (int)$request->input('category_images_count') : 1)
                : ($currentExtras['category_images_count'] ?? 1),
        ];
        
        $settings->template = 'common';
        $settings->extras = $newExtrasData;

        try {
            $settings->save();
            \Alert::success(trans('image_generation_history.settings.success'))->flash();
        } catch (\Exception $e) {
            \Alert::error(trans('image_generation_history.settings.error'))->flash();
            \Log::error('Error saving image generation settings: ' . $e->getMessage());
        }
        
        return redirect()->back();
    }
}
