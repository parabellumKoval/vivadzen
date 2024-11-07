<?php

namespace App\Http\Controllers\Admin\Traits;

trait CategoryCrud {
    
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

    $this->crud->removeField('images');

    $this->crud->addField([
      'name' => 'no_medicine',
      'label' => 'Не является лекарственным средством',
      'type' => 'checkbox',
      'default' => 1,
      'fake' => true,
      'store_in' => 'extras',
      'hint' => 'Выводить сообщение во всех товарах этой категории?',
      'tab' => 'Дополнительно'
    ]);

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
          'prefix' =>  config('backpack.store.category.image.base_path', '/')
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