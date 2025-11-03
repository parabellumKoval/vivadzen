<?php

namespace App\Http\Controllers\Admin\Traits;

trait CategoryCrud {
  
  // Extends of SetupCreateOperation
  public function createOperation() {
    $this->crud->addField([
        'name' => 'on_main',
        'label' => 'Показывать на главной странице?',
        'type' => 'boolean',
        'default' => '0',
        'fake' => true,
        'store_in' => 'extras',
        'tab' => 'Дополнительно'
    ]);

    $this->crud->addField([
        'name' => 'short_description',
        'label' => 'Короткое описание',
        'fake' => true,
        'store_in' => 'extras_trans',
        'tab' => 'Дополнительно'
    ]);

    $this->crud->addField([
        'name' => 'caption',
        'label' => 'Промо заголовок',
        'fake' => true, 
        'store_in' => 'extras_trans',
        'tab' => 'Дополнительно'
    ]);

    // $this->crud->addField([
    //     'name' => 'caption',
    //     'label' => 'Промо заголовок',
    //     'fake' => true,
    //     'store_in' => 'extras_trans',
    //     'tab' => 'Дополнительно'
    // ]);

    $this->crud->addField([
        'name' => 'full_description',
        'label' => 'Полное описание',
        'type' => 'ckeditor',
        'fake' => true,
        'store_in' => 'extras_trans',
        'tab' => 'Дополнительно'
    ]);

  }
  
  public function listOperation() {
  }

}