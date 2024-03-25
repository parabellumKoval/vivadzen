<?php

namespace App\Http\Controllers\Admin\Traits;

trait ReviewCrud {
  
  // Extends of SetupOperation
  public function setupOperation() {}

  // Extends of ListOperation
  public function listOperation() {}

  // Extends of CreateOperation
  public function createOperation() {

    $this->crud->addField([
      'name' => 'link',
      'label' => 'Ссылка на страницу автора',
      'fake' => true,
      'store_in' => 'extras'
    ])->afterField('owner');

    $this->crud->addField([
      'name' => 'advantages',
      'label' => 'Достоинства',
      'type' => 'textarea',
      'fake' => true,
      'store_in' => 'extras'
    ])->afterField('text');

    $this->crud->addField([
      'name' => 'flaws',
      'label' => 'Недостатки',
      'type' => 'textarea',
      'fake' => true,
      'store_in' => 'extras'
    ])->afterField('text');

    $this->crud->addField([
      'name' => 'verified_purchase',
      'label' => 'Подтвержденная покупка',
      'type' => 'boolean',
      'fake' => true,
      'store_in' => 'extras'
    ])->afterField('text');

  }

}