<?php

namespace App\Http\Controllers\Admin\Traits;

trait ProductCrud {

  use \Backpack\Tag\app\Traits\TagFields;
  
  public function listOperation(){

    $this->setupFilers();
    $this->setupTagColumns();

    $this->crud->removeColumn('categories');

    $this->crud->addColumn([
      'name' => 'adminProps',
      'label' => '🎚',
      'type' => 'textarea',
      'limit' => 100,
      'priority' => 1,
    ])->afterColumn('adminName');
  }
  
  // Extends of SetupCreateOperation
  public function createOperation() {

    // $this->setupTagFields();
    // $this->crud->modifyField('tags', [
    //   'tab' => trans('backpack-store::product-field.tabs.main')
    // ]);

    //
    // $this->crud->removeField('images');

    // IMAGES
    // $this->crud->addField([
    //   'name'  => 'images',
    //   'label' => 'Изображения',
    //   'type'  => 'repeatable',
    //   'fields' => [
    //     [
    //       'name' => 'src',
    //       'label' => 'Изображение',
    //       'type' => 'image',
    //       'crop' => false, // set to true to allow cropping, false to disable
    //       'prefix' =>  config('dress.store.product.image.base_path', '/')
    //     ],
    //     [
    //       'name' => 'alt',
    //       'label' => 'alt'
    //     ],
    //     [
    //       'name' => 'title',
    //       'label' => 'title'
    //     ],
    //     [
    //       'name' => 'size',
    //       'type' => 'radio',
    //       'label' => 'Размер',
    //       'options' => [
    //         'cover' => 'Cover',
    //         'contain' => 'Contain'
    //       ],
    //       'inline' => true
    //     ]
    //   ],
    //   'new_item_label'  => 'Добавить изобрежение',
    //   'init_rows' => 1,
    //   'default' => [],
    //   'hint' => 'При добавлении новых изображений, сохранение товара будет происходить дольше, так как картинку загружаются в удаленное облако.',
    //   'tab' => trans('backpack-store::product-field.tabs.images')
    // ])->beforeField('suppliersData');

  }

}
