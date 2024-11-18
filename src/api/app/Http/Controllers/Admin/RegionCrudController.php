<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\RegionRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

use App\Models\Region;
/**
 * Class RegionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class RegionCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;
    

    private $filter_categories = [];

    private $available_languages = null;
    private $langs_list = null;

    public function setup()
    {
      $this->crud->setModel(Region::class);
      $this->crud->setRoute(config('backpack.base.route_prefix') . '/region');
      $this->crud->setEntityNameStrings('регион', 'регионы');

      $this->category_class = config('backpack.store.category.class');

      // $this->filter_categories = $this->category_class::withoutGlobalScopes()
      //       ->whereNull('parent_id')
      //       ->pluck('name', 'id')
      //       ->toArray();
      
      // $cat_ids = Region::where('category_id', '!=', null)->pluck('id')->toArray();

      $this->filter_categories = $this->category_class::query()->has('regions')->pluck('name', 'id')->toArray();
      
      $this->available_languages = config('backpack.crud.locales');
      $this->langs_list = array_keys($this->available_languages);
    }

    
    protected function setupListOperation()
    {
      $langs_list = $this->langs_list;

      // Filter by category
      $this->crud->addFilter([
        'name' => 'category',
        'label' => 'Категория',
        'type' => 'select2',
      ], function(){
        return $this->filter_categories;
      }, function($id){
        $this->crud->query->where('category_id', $id);
      });

      $this->crud->addFilter([
        'name' => 'is_active',
        'label' => 'Активная',
        'type' => 'select2',
      ], function(){
        return [
          0 => 'Не активная',
          1 => 'Активная',
        ];
      }, function($is_active){
        $this->crud->query = $this->crud->query->where('is_active', $is_active);
      });


      $this->crud->addFilter([
        'name' => 'is_seo',
        'label' => 'Заполнено SEO',
        'type' => 'select2',
      ], function(){
        return [
          0 => 'Не заполнено SEO',
          // 1 => 'Частично заполнено',
          2 => 'Заполнено SEO',
        ];
      }, function($is_seo){
        $locale = \Lang::locale();

        if($is_seo == 0) {
          $this->crud->query
            ->where('seo', null)
            ->orWhere(function ($query) use ($locale) {
              $query
                ->where("seo->{$locale}->meta_title", '=', null)
                ->where("seo->{$locale}->meta_description", '!=', null)
                ->where("seo->{$locale}->h1", '=', null);
            });
        }elseif($is_seo == 1){
        }elseif($is_seo == 2){
          $this->crud->query->where("seo->{$locale}->meta_title", '!=', null);
          $this->crud->query->orWhere("seo->{$locale}->meta_description", '!=', null);
          $this->crud->query->orWhere("seo->{$locale}->h1", '!=', null);
        }
      });

      // IS ACTIVE
      $this->crud->addColumn([
        'name' => 'is_active',
        'label' => '✅',
        'type' => 'check'
      ]);

      $this->crud->addColumn([
        'name' => 'name',
        'label' => 'Регион',
        'limit' => 200,
        'searchLogic' => function ($query, $column, $searchTerm) use($langs_list) {
          $query->where(function($query) use ($searchTerm, $langs_list){
            foreach($langs_list as $index => $lang_key) {
              $function_name = $index === 0? 'whereRaw': 'orWhereRaw';
              $query->{$function_name}('LOWER(JSON_EXTRACT(name, "$.' . $lang_key . '")) LIKE ? ', ['%'.trim(mb_strtolower($searchTerm)).'%']);
            }
          });
        },
      ]);
      
      $this->crud->addColumn([
        'name' => 'category',
        'label' => 'Категория',
        'type' => 'relationship',
      ]);
      

      $this->crud->addColumn([
        'name' => 'is_seo',
        'label' => 'SEO',
        'type' => 'model_function',
        'function_name' => 'getAdminColumnSeo',
        'limit' => 1000,
      ]);
    }

    protected function setupCreateOperation()
    {
      $this->crud->setValidation(RegionRequest::class);
      
      $this->crud->addFields([
        [
          'name' => 'is_active',
          'label' => 'Активна',
          'type' => 'boolean',
          'default' => '1',
          'tab' => 'Основное'
        ],
        [
          'name' => 'name',
          'label' => 'Название',
          'type' => 'text',
          'tab' => 'Основное'
        ],
        [
          'name' => 'slug',
          'label' => 'URL',
          'hint' => 'По умолчанию будет сгенерирован из названия.',
          'tab' => 'Основное'
        ],
        [
          'name' => 'category',
          'label' => 'Категория',
          'type' => 'relationship',
          'tab' => 'Основное'
        ],
        [
          'name' => 'content',
          'label' => 'Описание',
          'type' => 'ckeditor',
          'tab' => 'Основное'
        ],
        [
          'name' => 'h1',
          'label' => 'H1 заголовок',
          'fake' => true,
          'store_in' => 'seo',
          'tab' => 'SEO'
        ],
        [
          'name' => 'meta_title',
          'label' => 'Meta title',
          'fake' => true,
          'store_in' => 'seo',
          'tab' => 'SEO'
        ],
        [
          'name' => 'meta_description',
          'label' => 'Meta description',
          'type' => 'textarea',
          'fake' => true,
          'store_in' => 'seo',
          'tab' => 'SEO'
        ]
      ]);

    }

    protected function setupUpdateOperation()
    {
      $this->setupCreateOperation();
    }
    

}
