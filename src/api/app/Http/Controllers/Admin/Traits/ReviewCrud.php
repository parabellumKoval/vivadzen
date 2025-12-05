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
      'label' => 'Ссылка на соцсеть',
      'type' => 'url',
      'fake' => true,
      'store_in' => 'extras',
      'attributes' => [
        'placeholder' => 'https://instagram.com/my_account'
      ],
      'wrapper' => [
        'class' => 'form-group col-md-6'
      ],
      'hint' => 'Используется для подтверждения подлинности отзыва и начисления бонусов.'
    ])->afterField('text');

    $this->crud->addField([
      'name' => 'advantages',
      'label' => 'Достоинства',
      'type' => 'textarea',
      'fake' => true,
      'store_in' => 'extras',
      'wrapper' => [ 
        'class' => 'form-group col-md-6'
      ]
    ])->afterField('link');

    $this->crud->addField([
      'name' => 'flaws',
      'label' => 'Недостатки',
      'type' => 'textarea',
      'fake' => true,
      'store_in' => 'extras',
      'wrapper' => [ 
        'class' => 'form-group col-md-6'
      ]
    ])->afterField('advantages');

    $this->crud->addField([
      'name' => 'verified_purchase',
      'label' => 'Подтвержденная покупка',
      'type' => 'boolean',
      'fake' => true,
      'store_in' => 'extras'
    ])->afterField('flaws');

  }

}
