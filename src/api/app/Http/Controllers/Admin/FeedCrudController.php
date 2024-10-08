<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

use App\Http\Requests\FeedRequest;
use App\Models\Feed;
/**
 * Class FeedCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class FeedCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
      $this->crud->setModel(Feed::class);
      $this->crud->setRoute(config('backpack.base.route_prefix') . '/feed');
      $this->crud->setEntityNameStrings('выгрузка', 'выгрузки');
    }

    protected function setupListOperation()
    {
      $this->crud->addColumn([
        'name' => 'is_active',
        'label' => '✅',
        'type' => 'check'
      ]);

      $this->crud->addColumn([
        'name' => 'name',
        'label' => 'Название'
      ]);
    }

    protected function setupCreateOperation()
    {
      $this->crud->setValidation(FeedRequest::class);

      // IS ACTIVE
      $this->crud->addField([
        'name' => 'is_active',
        'label' => 'Активен',
        'type' => 'boolean',
        'default' => '1',
        'tab' => 'Основное'
      ]);
      
      // NAME
      $this->crud->addField([
        'name' => 'name',
        'label' => 'Название',
        'type' => 'text',
        'tab' => 'Основное'
      ]);

      // Key
      $this->crud->addField([
        'name' => 'key',
        'label' => 'Ключ',
        'type' => 'text',
        'hint' => 'Уникальный идентификатор (название/ключ на латинице) этой выгрузки. Не должен совпадать с ключами других выгрузок. После создания не подлежит редактированию.',
        'tab' => 'Основное'
      ]);

      // CATEGORIES
      $this->crud->addField([
        'name' => 'delim_cats',
        'type' => 'custom_html',
        'value' => '<h3>Настройки соответствия категорий</h3>
          <p class="help-block">Для того, чтобы товары автоматически помещались в группы на PROM, 
          необходимо сперва установить соответствие между группами PROM и
          аналогичными категориями на сайте.</p>',
        'tab' => 'Настройки категорий'
      ]);

      $this->crud->addField([
        'name' => 'categoriesData',
        'label' => 'Соответствие категорий',
        'type' => 'repeatable',
        'fields' => [
          [
            'name' => 'prom_name',
            'label' => 'Категория (PROM)',
            'type' => 'text',
            'wrapper'   => [ 
              'class' => 'form-group col-md-6'
            ],
          ],[
            'name' => 'prom_id',
            'label' => 'ID (PROM)',
            'type' => 'text',
            'wrapper'   => [ 
              'class' => 'form-group col-md-6'
            ],
          ],[
            'label'  => "Категория на сайте",
            'type' => "select2_from_ajax",
            'name' => 'category_id',
            'entity' => 'categories',
            'placeholder' => "Select a category", 
            'model' => 'Backpack\Store\app\Models\Category',
            'attribute' => "name",
            'minimum_input_length' => 2,
            'data_source' => url("/admin/api/category"),
            'wrapper'   => [ 
              'class' => 'form-group col-md-12'
            ],
          ]
        ],
        'wrapper' => [
          'data-target' => 'repeatable-el',
        ],
        'new_item_label'  => 'Добавить категорию',
        'init_rows' => 0,
        'min_rows' => 0,
        'tab' => 'Настройки категорий'
      ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
