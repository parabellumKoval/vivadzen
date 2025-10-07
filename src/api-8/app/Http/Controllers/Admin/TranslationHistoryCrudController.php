<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Backpack\Settings\app\Models\Settings;

use App\Models\TranslationHistory;
/**
 * Class TranslationHistoryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class TranslationHistoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;
    


    private $available_languages = null;
    private $langs_list = null;

    public function setup()
    {
      $this->crud->setModel(TranslationHistory::class);
      $this->crud->setRoute(config('backpack.base.route_prefix') . '/translation-history');
      $this->crud->setEntityNameStrings('перевод', 'переводы deepL');

      $this->available_languages = config('backpack.crud.locales');
      $this->langs_list = array_keys($this->available_languages);
    }

    
    protected function setupListOperation()
    {
      $langs_list = $this->langs_list;
      $settings = Settings::where('key', 'deep_l_translations')->first();

      // Указываем кастомное представление для операции List
      $this->crud->setListView('crud::translation_history_list');

      $widget_definition_array = [
        'type'     => 'view',
        'view'     => 'crud::widgets.translate_settings',
        'position' => 'before_content',
        'data' =>  $settings->extras ?? null,
      ];

      Widget::add($widget_definition_array);
     
      $this->crud->addFilter([
        'name' => 'translatable_type',
        'label' => 'Тип',
        'type' => 'select2',
      ], function(){
        return TranslationHistory::TYPES;
      }, function($type){
       $this->crud->query->where('translatable_type', $type);
      });

      $this->crud->addFilter([
        'name' => 'status',
        'label' => 'Статус',
        'type' => 'select2',
      ], function(){
        return __('status.common');
      }, function($status){
       $this->crud->query->where('status', $status);
      });


      $this->crud->addColumn([
        'name' => 'translatable_type',
        'label' => 'Тип объекта',
        'type' => 'select_from_array',
        'options' => TranslationHistory::TYPES
      ]);

      $this->crud->addColumn([
        'name' => 'targetLinkAdmin',
        'label' => 'Объект перевода',
        'type' => 'textarea',
        'limit' => 300,
        'priority' => 1,
      ]);

    
      $this->crud->addColumn([
        'name' => 'statusHtml',
        'label' => 'Статус',
        'limit'  => 1200,
        'escaped' => false
      ]);

      $this->crud->addColumn([
        'name' => 'messageAdmin',
        'label' => 'Сообщение',
        'limit' => 300,
      ]);
      
      $this->crud->addColumn([
        'name' => 'updated_at',
        'label' => 'Дата',
      ]);
    }

    protected function setupCreateOperation()
    {
      $this->crud->setValidation(RegionRequest::class);
      
    }

    protected function setupUpdateOperation()
    {
      $this->setupCreateOperation();
    }
        
    /**
     * Method saveSettings
     *
     * @param Request $request [explicite description]
     *
     * @return void
     */
    public function saveSettings(Request $request) {
        // Найти или создать модель Settings
        $settings = Settings::firstOrNew(['key' => 'deep_l_translations']);
    
        // Получить текущие значения из поля extras
        $currentExtras = $settings->extras ?? [];
    
        // Подготовить новые данные для обновления
        $newExtrasData = [
            'auto_translate_enabled' => $request->has('auto_translate_enabled') ? (bool)$request->input('auto_translate_enabled') : ($currentExtras['auto_translate_enabled'] ?? false),
            'translate_products' => $request->has('translate_products') ? (bool)$request->input('translate_products') : false,
            'translate_specs' => $request->has('translate_specs') ? (bool)$request->input('translate_specs') : false,
            'active_products_only' => $request->has('active_products_only') ? (bool)$request->input('active_products_only') : ($currentExtras['active_products_only'] ?? false),
            'in_stock_products_only' => $request->has('in_stock_products_only') ? (bool)$request->input('in_stock_products_only') : ($currentExtras['in_stock_products_only'] ?? false),
            'min_price' => $request->has('min_price') ? (int)$request->input('min_price') : ($currentExtras['min_price'] ?? 0),
            'min_symbols' => $request->has('min_symbols') ? (int)$request->input('min_symbols') : ($currentExtras['min_symbols'] ?? 0),
            'translate_attribute_names' => $request->has('translate_attribute_names') ? (bool)$request->input('translate_attribute_names') : ($currentExtras['translate_attribute_names'] ?? false),
            'translate_attribute_values' => $request->has('translate_attribute_values') ? (bool)$request->input('translate_attribute_values') : ($currentExtras['translate_attribute_values'] ?? false),
            'translate_product_attribute_values' => $request->has('translate_product_attribute_values') ? (bool)$request->input('translate_product_attribute_values') : ($currentExtras['translate_product_attribute_values'] ?? false),
            'unique_product_specs' => $request->has('unique_product_specs') ? (bool)$request->input('unique_product_specs') : ($currentExtras['unique_product_specs'] ?? false),
            'translate_brands' => $request->has('translate_brands') ? (bool)$request->input('translate_brands') : false,
            'active_brands_only' => $request->has('active_brands_only') ? (bool)$request->input('active_brands_only') : ($currentExtras['active_brands_only'] ?? false),
        ];
    
        // Обновить поле extras
        $settings->template = 'common';
        $settings->extras = $newExtrasData;
        $settings->save();
    
        return redirect()->back();
    }
}
