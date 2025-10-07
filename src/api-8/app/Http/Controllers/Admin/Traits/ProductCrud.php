<?php

namespace App\Http\Controllers\Admin\Traits;

trait ProductCrud {

  use \Backpack\Tag\app\Traits\TagFields;
    
  /**
   * listOperation
   *
   * @return void
   */
  public function listOperation() {

    $this->available_languages = config('backpack.crud.locales');
    $this->langs_list = array_keys($this->available_languages);

    $this->setupFilers();
    $this->setupTagColumns();

    $this->crud->addFilter([
      'name' => 'is_trans',
      'label' => 'Переведено DeepL',
      'type' => 'simple',
    ], false,
     function(){
      $this->crud->query->where('is_trans', 1);
    });


    // 
    $this->crud->removeColumn('name');

    //
    $this->crud->removeColumn('categories');

    $this->crud->addColumn([
      'name' => 'adminName',
      'label' => 'Название',
      'type' => 'textarea',
      'limit' => 100,
      'priority' => 1,
      'searchLogic' => function ($query, $column, $searchTerm) {
        $query->orWhere(function($query) use ($searchTerm){
          foreach($this->langs_list as $index => $lang_key) {
            $function_name = $index === 0? 'whereRaw': 'orWhereRaw';
            $query->{$function_name}('LOWER(JSON_EXTRACT(name, "$.' . $lang_key . '")) LIKE ? ', ['%'.trim(mb_strtolower($searchTerm)).'%']);
          }
        });
      },
    ])->afterColumn('is_active');



    $this->crud->addColumn([
      'name' => 'adminProps',
      'label' => '🎚',
      'type' => 'textarea',
      'limit' => 100,
      'priority' => 1,
    ])->afterColumn('adminName');
  }

  /**
   * createOperation
   *
   * @return void
   */
  public function createOperation() {

    // Extends of SetupCreateOperation
    $entry_id = \Route::current()->parameter('id');
    $this->entry = !empty($entry_id)? $this->crud->getEntry($entry_id): null;

    // Tags
    $this->setupTagFields();

    // CUSTOM PROPERTIES
    $this->crud->addField([
      'name' => 'delim_0',
      'type' => 'custom_html',
      'value' => '<h3>Особенности</h3>
        <p class="help-block">Выводится в виде тегов с иконками на странице товара.</p>',
      'tab' => trans('backpack-store::product-field.tabs.characteristics')
    ])->beforeField('delim');

    $this->crud->addField([
      'name' => 'specs',
      'type' => 'hidden_fake_array',
      'value' => null,
      'fake' => true,
      'store_in' => 'extras',
      'tab' => trans('backpack-store::product-field.tabs.characteristics')
    ])->beforeField('delim');

    $this->crud->addField([
      'name' => 'specs[natural]',
      'label' => 'Натуральный продукт',
      'type' => 'checkbox',
      'value' => $this->entry->specs['natural'] ?? 0,
      'wrapper'   => [ 
        'class' => 'form-group col-md-6'
      ],
      'tab' => trans('backpack-store::product-field.tabs.characteristics')
    ])->beforeField('delim');

    $this->crud->addField([
      'name' => 'specs[vegetarian]',
      'label' => 'Подходит для вегетарианцев',
      'type' => 'checkbox',
      'value' => $this->entry->specs['vegetarian'] ?? 0,
      'wrapper'   => [ 
        'class' => 'form-group col-md-6'
      ],
      'tab' => trans('backpack-store::product-field.tabs.characteristics')
    ])->beforeField('delim');

    $this->crud->addField([
      'name' => 'specs[lactose]',
      'label' => 'Без лактозы',
      'type' => 'checkbox',
      'value' => $this->entry->specs['lactose'] ?? 0,
      'wrapper'   => [ 
        'class' => 'form-group col-md-6'
      ],
      'tab' => trans('backpack-store::product-field.tabs.characteristics')
    ])->beforeField('delim');

    $this->crud->addField([
      'name' => 'specs[gluten]',
      'label' => 'Без глютена',
      'type' => 'checkbox',
      'value' => $this->entry->specs['gluten'] ?? 0,
      'wrapper'   => [ 
        'class' => 'form-group col-md-6'
      ],
      'tab' => trans('backpack-store::product-field.tabs.characteristics')
    ])->beforeField('delim');

    $this->crud->addField([
      'name' => 'specs[gmo]',
      'label' => 'Без ГМО',
      'type' => 'checkbox',
      'value' => $this->entry->specs['gmo'] ?? 0,
      'wrapper'   => [ 
        'class' => 'form-group col-md-6'
      ],
      'tab' => trans('backpack-store::product-field.tabs.characteristics')
    ])->beforeField('delim');

    $this->crud->addField([
      'name' => 'specs[milk]',
      'label' => 'Без молока',
      'type' => 'checkbox',
      'value' => $this->entry->specs['milk'] ?? 0,
      'wrapper'   => [ 
        'class' => 'form-group col-md-6'
      ],
      'tab' => trans('backpack-store::product-field.tabs.characteristics')
    ])->beforeField('delim');


    //
    $this->crud->removeField('images');

    // IMAGES
    $this->crud->addField([
      'name'  => 'images',
      'label' => 'Изображения',
      'type'  => 'repeatable',
      'fields' => [
        [
          'name' => 'src',
          'label' => 'Изображение',
          'type' => 'image',
          'crop' => false, // set to true to allow cropping, false to disable
          'prefix' =>  config('backpack.store.product.image.base_path', '/')
        ],
        [
          'name' => 'alt',
          'label' => 'alt'
        ],
        [
          'name' => 'title',
          'label' => 'title'
        ],
        [
          'name' => 'size',
          'type' => 'radio',
          'label' => 'Размер',
          'options' => [
            'cover' => 'Cover',
            'contain' => 'Contain'
          ],
          'inline' => true
        ]
      ],
      'new_item_label'  => 'Добавить изобрежение',
      'init_rows' => 1,
      'default' => [],
      'hint' => 'При добавлении новых изображений, сохранение товара будет происходить дольше, так как картинку загружаются в удаленное облако.',
      'tab' => trans('backpack-store::product-field.tabs.images')
    ])->beforeField('suppliersData');
  }

}