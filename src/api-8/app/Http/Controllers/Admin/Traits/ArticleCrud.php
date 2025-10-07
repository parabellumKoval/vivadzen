<?php

namespace App\Http\Controllers\Admin\Traits;

trait ArticleCrud {
  
  public function listOperation() {}
  public function updateOperation() {}

  // Extends of SetupCreateOperation
  public function createOperation() {
  
    $this->crud->addField([
      'name'  => 'time',
      'label' => 'Время чтения',
      'type'  => 'number',
      'store_in' => 'extras',
      'fake' => true,
      'tab' => 'Основное'
    ]);


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
          'prefix' =>  config('backpack.articles.image.base_path', '/')
        ],
        [
          'name' => 'alt',
          'label' => 'alt'
        ],
        [
          'name' => 'title',
          'label' => 'title',
          'type' => 'text'
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
