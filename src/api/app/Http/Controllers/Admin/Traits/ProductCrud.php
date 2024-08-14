<?php

namespace App\Http\Controllers\Admin\Traits;

trait ProductCrud {
    
  /**
   * listOperation
   *
   * @return void
   */
  public function listOperation() {
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


    // CUSTOM PROPERTIES
    $this->crud->addField([
      'name' => 'delim_0',
      'type' => 'custom_html',
      'value' => '<h3>Особенности</h3>
        <p class="help-block">Выводится в виде тегов с иконками на странице товара.</p>',
      'tab' => 'Характеристики'
    ])->beforeField('delim');

    $this->crud->addField([
      'name' => 'specsvirtual',
      'type' => 'hidden',
      'value' => 'specs'
    ]);

    $this->crud->addField([
      'name' => 'specs[natural]',
      'label' => 'Натуральный продукт',
      'type' => 'checkbox',
      'value' => $this->entry->specs['natural'] ?? 0,
      'wrapper'   => [ 
        'class' => 'form-group col-md-6'
      ],
      'tab' => 'Характеристики'
    ])->beforeField('delim');

    $this->crud->addField([
      'name' => 'specs[vegetarian]',
      'label' => 'Подходит для вегетарианцев',
      'type' => 'checkbox',
      'value' => $this->entry->specs['vegetarian'] ?? 0,
      'wrapper'   => [ 
        'class' => 'form-group col-md-6'
      ],
      'tab' => 'Характеристики'
    ])->beforeField('delim');

    $this->crud->addField([
      'name' => 'specs[lactose]',
      'label' => 'Без лактозы',
      'type' => 'checkbox',
      'value' => $this->entry->specs['lactose'] ?? 0,
      'wrapper'   => [ 
        'class' => 'form-group col-md-6'
      ],
      'tab' => 'Характеристики'
    ])->beforeField('delim');

    $this->crud->addField([
      'name' => 'specs[gluten]',
      'label' => 'Без глютена',
      'type' => 'checkbox',
      'value' => $this->entry->specs['gluten'] ?? 0,
      'wrapper'   => [ 
        'class' => 'form-group col-md-6'
      ],
      'tab' => 'Характеристики'
    ])->beforeField('delim');

    $this->crud->addField([
      'name' => 'specs[gmo]',
      'label' => 'Без ГМО',
      'type' => 'checkbox',
      'value' => $this->entry->specs['gmo'] ?? 0,
      'wrapper'   => [ 
        'class' => 'form-group col-md-6'
      ],
      'tab' => 'Характеристики'
    ])->beforeField('delim');

    $this->crud->addField([
      'name' => 'specs[milk]',
      'label' => 'Без молока',
      'type' => 'checkbox',
      'value' => $this->entry->specs['milk'] ?? 0,
      'wrapper'   => [ 
        'class' => 'form-group col-md-6'
      ],
      'tab' => 'Характеристики'
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
      'tab' => 'Изображения'
    ]);
  
  }

}