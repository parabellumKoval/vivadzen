<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

use App\Http\Requests\PromptRequest;
use App\Models\Prompt;
/**
 * Class PromptCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class PromptCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
      $this->crud->setModel(Prompt::class);
      $this->crud->setRoute(config('backpack.base.route_prefix') . '/prompt');
      $this->crud->setEntityNameStrings('ai prompt', 'ai prompts');
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

      $this->crud->addColumn([
        'name' => 'content',
        'label' => 'Промпт'
      ]);

      $this->crud->addColumn([
        'name' => 'categories',
        'label' => 'Категории',
        'type' => 'relationship',
      ]);
    }

    protected function setupCreateOperation()
    {
      $this->crud->setValidation(PromptRequest::class);

      // IS ACTIVE
      $this->crud->addField([
        'name' => 'is_active',
        'label' => 'Активен',
        'type' => 'boolean',
        'default' => '1',
      ]);
      
      // NAME
      $this->crud->addField([
        'name' => 'name',
        'label' => 'Название',
        'type' => 'text',
        'hint' => 'Название промпта для удобства (необязательное поле)'
      ]);

      $this->crud->addField([
        'name' => 'categories',
        'label' => 'Категории',
        'type' => 'select2_multiple',
        'entity' => 'categories',
        'attribute' => 'name',
        'model' => 'App\Models\Category',
        // 'value' => $this->categories? $this->categories: null,
        'hint' => 'Выберите категории товаров к которым будет применен данный промпт. Если выбрать корневую категорию, промпт будет применен и ко всем внутрениим категориям.'
      ]);

      // Key
      $this->crud->addField([
        'name' => 'content',
        'label' => 'Prompt',
        'type' => 'textarea',
        'attributes' => [
          'rows' => 20
        ]
      ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
