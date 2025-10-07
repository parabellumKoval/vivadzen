<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

use App\Models\Payment;

/**
 * Class PaymentCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class PaymentCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
      $this->crud->setModel(Payment::class);
      $this->crud->setRoute(config('backpack.base.route_prefix') . '/payment');
      $this->crud->setEntityNameStrings('платеж', 'платежі');
    }

    protected function setupListOperation()
    {

      $this->crud->addColumn([
        'name' => 'orderLink',
        'label' => 'Заказ',
        'limit'  => 1200,
        'escaped' => false
      ]);
      
      $this->crud->addColumn([
        'name' => 'statusHtml',
        'label' => 'Статус',
        'limit'  => 1200,
        'escaped' => false
      ]);

      $this->crud->addColumn([
        'name' => 'amount',
        'label' => 'Сумма',
        'type' => 'number',
        'suffix'        => ' UAH',
        'decimals'      => 2,
        'dec_point'     => ',',
        'thousands_sep' => '.',
      ]);

      $this->crud->addColumn([
        'name' => 'created_at',
        'label' => 'Дата',
        'type' => 'datetime'
      ]);
    }

    protected function setupShowOperation()
    {
      $this->crud->addColumn([
        'name' => 'status',
        'label' => 'Статус',
      ]);

      $this->crud->addColumn([
        'name' => 'order',
        'label' => 'Заказ',
        'type' => 'relationship'
      ]);

      $this->crud->addColumn([
        'name' => 'amount',
        'label' => 'Сумма'
      ]);


      $this->crud->addColumn([
        'name' => 'extras',
        'label' => 'Детали',
        // 'type' => 'array'
        'type'  => 'model_function',
        'function_name' => 'getExtrasData',
      ]);
    }

    protected function setupCreateOperation()
    {
    }

    protected function setupUpdateOperation()
    {
    }
}
