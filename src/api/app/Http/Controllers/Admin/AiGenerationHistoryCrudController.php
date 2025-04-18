<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Backpack\Settings\app\Models\Settings;

use App\Models\AiGenerationHistory;
/**
 * Class AiGenerationHistoryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class AiGenerationHistoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        $this->crud->setModel(AiGenerationHistory::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/ai-generation-history');
        $this->crud->setEntityNameStrings(
            trans('ai_generation_history.entity_name.singular'),
            trans('ai_generation_history.entity_name.plural')
        );
    }

    protected function setupListOperation()
    {
        $settings = Settings::where('key', 'ai_generation_settings')->first();

        // Add widget for AI generation settings
        $widget_definition_array = [
            'type'     => 'view',
            'view'     => 'crud::widgets.ai_generation_settings',
            'position' => 'before_content',
            'data'     => $settings->extras ?? null,
        ];

        Widget::add($widget_definition_array);

        $this->crud->addFilter([
            'name' => 'generatable_type',
            'label' => trans('ai_generation_history.filters.type'),
            'type' => 'select2',
        ], function () {
            return AiGenerationHistory::TYPES;
        }, function ($type) {
            $this->crud->query->where('generatable_type', $type);
        });

        $this->crud->addFilter([
            'name' => 'status',
            'label' => trans('ai_generation_history.filters.status'),
            'type' => 'select2',
        ], function () {
            return __('status.common');
        }, function ($status) {
            $this->crud->query->where('status', $status);
        });

        $this->crud->addColumn([
            'name' => 'generatable_type',
            'label' => trans('ai_generation_history.columns.generatable_type'),
            'type' => 'select_from_array',
            'options' => AiGenerationHistory::TYPES
        ]);

        $this->crud->addColumn([
            'name' => 'targetLinkAdmin',
            'label' => trans('ai_generation_history.columns.generatable'),
            'type' => 'textarea',
            'limit' => 300,
            'priority' => 1,
        ]);

        $this->crud->addColumn([
            'name' => 'statusHtml',
            'label' => trans('ai_generation_history.columns.status'),
            'limit' => 1200,
            'escaped' => false
        ]);

        $this->crud->addColumn([
            'name' => 'messageAdmin',
            'label' => trans('ai_generation_history.columns.message'),
            'limit' => 300,
        ]);

        $this->crud->addColumn([
            'name' => 'updated_at',
            'label' => trans('ai_generation_history.columns.updated_at'),
        ]);
    }
    
    /**
     * Save AI generation settings
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveAiGenerationSettings(Request $request) {
        try {
            $settings = Settings::firstOrNew(['key' => 'ai_generation_settings']);
            
            $currentExtras = $settings->extras ?? [];
            
            $autoGenEnabled = $request->has('auto_generation_enabled') 
                ? (bool)$request->input('auto_generation_enabled') 
                : false;

            $generateForCategories = $autoGenEnabled 
                ? $request->has('generate_for_categories')? (bool)$request->input('generate_for_categories'): false
                : ($currentExtras['generate_for_categories'] ?? false);

            $newExtrasData = [
                // General settings
                'auto_generation_enabled' => $autoGenEnabled,

                // Product settings
                'generate_description' => $autoGenEnabled
                    ? ($request->has('generate_description') ? (bool)$request->input('generate_description') : false)
                    : ($currentExtras['generate_description'] ?? false),
                'detect_brand' => $autoGenEnabled
                    ? ($request->has('detect_brand') ? (bool)$request->input('detect_brand') : false)
                    : ($currentExtras['detect_brand'] ?? false),
                'detect_category' => $autoGenEnabled
                    ? ($request->has('detect_category') ? (bool)$request->input('detect_category') : false)
                    : ($currentExtras['detect_category'] ?? false),
                'fill_characteristics' => $autoGenEnabled
                    ? ($request->has('fill_characteristics') ? (bool)$request->input('fill_characteristics') : false)
                    : ($currentExtras['fill_characteristics'] ?? false),

                'active_products_only' => $autoGenEnabled
                    ? ($request->has('active_products_only') ? (bool)$request->input('active_products_only') : false)
                    : ($currentExtras['active_products_only'] ?? false),
                'in_stock_products_only' => $autoGenEnabled
                    ? ($request->has('in_stock_products_only') ? (bool)$request->input('in_stock_products_only') : false)
                    : ($currentExtras['in_stock_products_only'] ?? false),
                'min_price' => $autoGenEnabled
                    ? ($request->has('min_price') ? (int)$request->input('min_price') : 0)
                    : ($currentExtras['min_price'] ?? 0),

                // Category settings
                'generate_for_categories' => $generateForCategories,
                'active_categories_only' => ($autoGenEnabled && $generateForCategories)
                    ? ($request->has('active_categories_only') ? (bool)$request->input('active_categories_only') : false)
                    : ($currentExtras['active_categories_only'] ?? false),
                'categories_with_products_only' => ($autoGenEnabled && $generateForCategories)
                    ? ($request->has('categories_with_products_only') ? (bool)$request->input('categories_with_products_only') : false)
                    : ($currentExtras['categories_with_products_only'] ?? false),
            ];

        
            // Update settings
            $settings->template = 'common';
            $settings->extras = $newExtrasData;
            $settings->save();
            
            \Alert::success(trans('ai_generation_history.settings.success'))->flash();
        } catch (\Exception $e) {
            \Alert::error(trans('ai_generation_history.settings.error'))->flash();
        }
        
        return redirect()->back();
    }
}
