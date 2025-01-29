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

    $this->setupFilers();
    $this->setupTagColumns();
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


    // Ai Content
    $this->crud->addField([
      'name' => 'is_ai_content_virtual',
      'label' => 'Сгенерирован ИИ',
      'type' => 'checkbox',
      'tab' => 'Основное',
      'hint' => 'Был ли контент сгенерирован ИИ (раздел в админке AI Prompts)',
    ])->afterField('content');

    // PROM CATEGORY
    $this->crud->addField([
      'name' => 'category_feed_id',
      'label' => 'Категория на PROM',
      'type' => 'select2',
      'entity' => 'prom_category',
      'attribute' => 'prom_name',
      'model' => 'App\Models\CategoryFeed',
      'tab' => 'Основное',
      'hint' => 'Укажите если необходимо однозначно привязать товар к категории на PROM (иначе будут применены общие правила)',
    ])->afterField('categories');

    // Tags
    $this->setupTagFields();

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
      'value' => 'specs',
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
      'name'  => 'is_images_generated_virtual',
      'label' => 'Изображения были заполнены автоматически',
      'type' => 'checkbox',
      // 'fake' => true, 
      // 'store_in' => 'extras',
      'tab' => 'Изображения'
    ]);

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
  


    $this->crud->addField([
      'name' => 'delim_duplic',
      'type' => 'custom_html',
      'value' => '<h3>Дубликаты</h3>
        <p class="help-block">В данном разделе можно "сшивать" несколько товаров в один. Для того чтобы это сделать:</p>
        <ol>
          <li>В поле ниже выберите основной товар, то есть тот товар дубликатом которого явялется товар, который вы сейчас редактируете.</li>
          <li>В течении 1 часа этот товар автоматически будет объединен с указанным в поле ниже.</li>
          <li>Этот товар будет полностью удален, а информация о складе (поставщик, артикул, наличие, цена...) будет перенесена в карточку основного товара.</li>
        </ol>
      ',
      'tab' => 'Дубликаты'
    ])->afterField('images');

    $this->crud->addField([
      'name' => 'duplicate_of',
      'label' => 'Выберите товар',
      'type'    => 'relationship',
      'model'     => 'Backpack\Store\app\Models\Product',
      'attribute' => 'name',
      'ajax' => true,
      'multiple' => false,
      // 'entity' => Backpack\Store\app\Models\Product::class,
      'entity' => 'duplicate',
      'data_source' => url("/admin/api/product"),
      'placeholder' => "Поиск по названию товара",
      'minimum_input_length' => 0,
      'hint' => 'Выберите товар дубликатом которого является данный товар.',
      'tab' => 'Дубликаты'
    ]);
  }

}