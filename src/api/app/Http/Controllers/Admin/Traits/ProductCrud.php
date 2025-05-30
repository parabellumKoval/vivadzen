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
      'name' => 'is_ai_content',
      'label' => 'OpenAi контент',
      'type' => 'simple',
    ], false,
     function(){
      $this->crud->query->where('extras->is_ai_content', 1);
    });

    $this->crud->addFilter([
      'name' => 'is_images_generated',
      'label' => 'Serper изображения',
      'type' => 'simple',
    ], false,
     function(){
      $this->crud->query->where('extras->is_images_generated', 1);
    });


    // $this->crud->addFilter([
    //   'name' => 'is_images_generated',
    //   'label' => 'Serper изображения',
    //   'type' => 'simple',
    // ], false,
    //  function(){
    //   $this->crud->query->where('extras->is_images_generated', 1);
    // });


    $this->crud->addFilter([
      'name' => 'is_trans',
      'label' => 'Переведено DeepL',
      'type' => 'simple',
    ], false,
     function(){
      $this->crud->query->where('is_trans', 1);
    });


    $this->crud->addFilter([
      'name' => 'need_moderation',
      'label' => 'Требует модерации',
      'type' => 'simple',
    ], false,
     function(){
      $this->crud->query
        ->where(function($query) {
          $query->where('extras->is_ai_content', 1)
                  ->where(function ($subQuery) {
                    $subQuery->where('extras->ai_content_moderated', '!=', 1)
                            ->where('extras->ai_content_moderated', '!=', 'on')
                            ->orWhereNull('extras->ai_content_moderated');
                });
          })
        ->orWhere(function($query) {
          $query->where('extras->is_images_generated', 1)
                  ->where(function ($subQuery) {
                    $subQuery->where('extras->images_moderated', '!=', 1)
                            ->where('extras->images_moderated', '!=', 'on')
                            ->orWhereNull('extras->images_moderated');
                });
          })
        ->orWhere(function($query) {
          $query->where('extras->brand_ai_generated', 1)
                  ->where(function ($subQuery) {
                    $subQuery->where('extras->brand_ai_generated_moderated', '!=', 1)
                            ->where('extras->brand_ai_generated_moderated', '!=', 'on')
                            ->orWhereNull('extras->brand_ai_generated_moderated');
                });
          })
        ->orWhere(function($query) {
          $query->where('extras->category_ai_generated', 1)
                  ->where(function ($subQuery) {
                    $subQuery->where('extras->category_ai_generated_moderated', '!=', 1)
                            ->where('extras->category_ai_generated_moderated', '!=', 'on')
                            ->orWhereNull('extras->category_ai_generated_moderated');
                });
          })
        ->orWhere(function($query) {
          $query->where('extras->attributes_ai_generated', 1)
                  ->where(function ($subQuery) {
                    $subQuery->where('extras->attributes_ai_moderated', '!=', 1)
                            ->where('extras->attributes_ai_moderated', '!=', 'on')
                            ->orWhereNull('extras->attributes_ai_moderated');
                });
          })
        ->orWhere(function($query) {
          $query->where('extras->name_ai_generated', 1)
                  ->where(function ($subQuery) {
                    $subQuery->where('extras->name_ai_moderated', '!=', 1)
                            ->where('extras->name_ai_moderated', '!=', 'on')
                            ->orWhereNull('extras->name_ai_moderated');
                });
          })
        ->orWhere(function($query) {
          $query->where('extras->is_ai_merchant_content', 1)
                  ->where(function ($subQuery) {
                    $subQuery->where('extras->ai_merchant_content_moderated', '!=', 1)
                            ->where('extras->ai_merchant_content_moderated', '!=', 'on')
                            ->orWhereNull('extras->ai_merchant_content_moderated');
                });
          });
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

    // Ai Names
    $this->crud->addField([
      'name' => 'name_ai_generated',
      'label' => 'Название сгенерировано AI',
      'type' => 'checkbox',
      'tab' => trans('backpack-store::product-field.tabs.main'),
      'fake' => true, 
      'store_in' => 'extras',
      'hint' => 'Было ли название сгенерировано AI',
    ])->afterField('name');


    $this->crud->addField([
      'name' => 'name_ai_moderated',
      'label' => 'Проверен',
      'type' => 'moderation',
      'tab' => trans('backpack-store::product-field.tabs.main'),
      'wrap_items' => ['name', 'name_ai_generated'],
      'wrapper_class' => 'wrapper',
      'switch_class' => 'box-warning',
      'enabled_when' => 'name_ai_generated',
      'fake' => true, 
      'store_in' => 'extras',
    ]);

    // Ai Merchant content
    $this->crud->addField([
      'name' => 'is_ai_merchant_content',
      'label' => 'Описание для Google Merchant сгенерировано AI',
      'type' => 'checkbox',
      'tab' => trans('backpack-store::product-field.tabs.main'),
      'fake' => true, 
      'store_in' => 'extras',
      'hint' => 'Было ли описание Google Merchant сгенерировано AI',
    ])->afterField('merchant_content');


    $this->crud->addField([
      'name' => 'ai_merchant_content_moderated',
      'label' => 'Проверен',
      'type' => 'moderation',
      'tab' => trans('backpack-store::product-field.tabs.main'),
      'wrap_items' => ['merchant_content', 'is_ai_merchant_content'],
      'wrapper_class' => 'wrapper',
      'switch_class' => 'box-warning',
      'enabled_when' => 'is_ai_merchant_content',
      'fake' => true, 
      'store_in' => 'extras',
    ]);

    // Ai Content
    $this->crud->addField([
      'name' => 'is_ai_content',
      'label' => 'Сгенерирован AI',
      'type' => 'checkbox',
      'tab' => trans('backpack-store::product-field.tabs.main'),
      'fake' => true, 
      'store_in' => 'extras',
      'hint' => 'Был ли контент сгенерирован AI (раздел в админке AI Prompts)',
    ])->afterField('content');


    $this->crud->addField([
      'name' => 'ai_content_moderated',
      'label' => 'Проверен',
      'type' => 'moderation',
      'tab' => trans('backpack-store::product-field.tabs.main'),
      'wrap_items' => ['content', 'is_ai_content'],
      'wrapper_class' => 'wrapper',
      'switch_class' => 'box-warning',
      'enabled_when' => 'is_ai_content',
      'fake' => true, 
      'store_in' => 'extras',
    ]);

    // BRAND
    $this->crud->addField([
      'name' => 'brand_ai_generated',
      'label' => 'Бренд заполнен автоматически AI',
      'type' => 'checkbox',
      'tab' => trans('backpack-store::product-field.tabs.main'),
      'hint' => 'Был ли бренд заполнен автоматически AI',
      'fake' => true, 
      'store_in' => 'extras',
    ])->afterField('category_feed_id');

    $this->crud->addField([
      'name' => 'brand_ai_generated_moderated',
      'label' => 'Проверено',
      'type' => 'moderation',
      'tab' => trans('backpack-store::product-field.tabs.main'),
      'wrap_items' => ['brand', 'brand_ai_generated'],
      'wrapper_class' => 'wrapper',
      'switch_class' => 'box-warning',
      'enabled_when' => 'brand_ai_generated',
      'fake' => true, 
      'store_in' => 'extras',
    ]);

    // CATEGORY
    $this->crud->addField([
      'name' => 'category_ai_generated',
      'label' => 'Категория заполнена автоматически AI',
      'type' => 'checkbox',
      'tab' => trans('backpack-store::product-field.tabs.main'),
      'hint' => 'Была ли категория заполнена автоматически AI',
      'fake' => true, 
      'store_in' => 'extras',
    ])->afterField('categories');

    $this->crud->addField([
      'name' => 'category_ai_generated_moderated',
      'label' => 'Проверено',
      'type' => 'moderation',
      'tab' => trans('backpack-store::product-field.tabs.main'),
      'wrap_items' => ['categories', 'category_ai_generated'],
      'wrapper_class' => 'wrapper',
      'switch_class' => 'box-warning',
      'enabled_when' => 'category_ai_generated',
      'fake' => true, 
      'store_in' => 'extras',
    ]);

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
      'name'  => 'is_images_generated',
      'label' => 'Изображения заполнены AI',
      'type' => 'checkbox',
      'fake' => true, 
      'store_in' => 'extras',
      'hint' => 'Были ли изображения заполнены автоматически AI',
      'tab' => trans('backpack-store::product-field.tabs.images')
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
      'hint' => 'При добавлении новых изображений, сохранение товара будет происходить дольше, так как картинку загружаются в удаленное облако.',
      'tab' => trans('backpack-store::product-field.tabs.images')
    ]);

    $this->crud->addField([
      'name' => 'images_moderated',
      'label' => 'Проверено',
      'type' => 'moderation',
      'tab' => trans('backpack-store::product-field.tabs.images'),
      'wrap_items' => ['is_images_generated', 'images'],
      'wrapper_class' => 'wrapper',
      'switch_class' => 'box-warning',
      'enabled_when' => 'is_images_generated',
      'fake' => true, 
      'store_in' => 'extras',
    ]);
  
    // Attributes
    $this->crud->addField([
      'name'  => 'attributes_ai_generated',
      'label' => 'Атрибуты заполнены автоматически AI',
      'type' => 'checkbox',
      'fake' => true, 
      'store_in' => 'extras',
      'hint' => 'Были ли атрибуты заполнены автоматически AI',
      'tab' => trans('backpack-store::product-field.tabs.characteristics')
    ])->afterField('delim_2');


    $this->crud->addField([
      'name' => 'attributes_ai_moderated',
      'label' => 'Проверено',
      'type' => 'moderation',
      'tab' => trans('backpack-store::product-field.tabs.characteristics'),
      'wrap_items' => ['attributes_ai_generated', 'props'],
      'wrapper_class' => 'wrapper',
      'switch_class' => 'box-warning',
      'enabled_when' => 'attributes_ai_generated',
      'fake' => true, 
      'store_in' => 'extras',
    ]);

    // Duplicates
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
      'tab' => trans('backpack-store::product-field.tabs.management')
    ]);

    $this->crud->addField([
      'name' => 'delim_duplic',
      'type' => 'custom_html',
      'value' => '<h3>Дубликаты</h3>
        <p class="help-block">В данном разделе можно "сшивать" несколько товаров в один. Для того чтобы это сделать:</p>
        <ol>
          <li>В поле ниже выберите основной товар, то есть тот товар дубликатом которого является товар, который вы сейчас редактируете.</li>
          <li>В течении 1 часа этот товар автоматически будет объединен с указанным в поле ниже.</li>
          <li>Этот товар будет полностью удален, а информация о складе (поставщик, артикул, наличие, цена...) будет перенесена в карточку основного товара.</li>
        </ol>
      ',
      'tab' => trans('backpack-store::product-field.tabs.management')
    ]);


    // PROM CATEGORY
    $this->crud->addField([
      'name' => 'category_feed_id',
      'label' => 'Категория на PROM',
      'type' => 'select2',
      'entity' => 'prom_category',
      'attribute' => 'prom_name',
      'model' => 'App\Models\CategoryFeed',
      'tab' => trans('backpack-store::product-field.tabs.management'),
      'hint' => 'Укажите если необходимо однозначно привязать товар к категории на PROM (иначе будут применены общие правила)',
    ]);
  }

}