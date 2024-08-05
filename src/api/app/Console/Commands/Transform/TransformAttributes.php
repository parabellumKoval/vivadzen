<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;
use Backpack\Store\app\Models\Attribute;
use Backpack\Store\app\Models\AttributeValue;
use Backpack\Store\app\Models\AttributeProduct;

ini_set('memory_limit', '500M');

class TransformAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:transform-attributes {method?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

      $method = $this->argument('method');

      if($method) {
        $this->{$method}();
      }else {
        $this->moveAttributesToCustom();
        $this->mergeAtrributes();
      }

      return 0;
    }
    
    
    /**
     * moveAttributesToCustom
     *
     * @return void
     */
    private function moveAttributesToCustom() {
      $attr_names = [
        "Безопасна при проглатывании",
        "Вариации названия:",
        "Вид животных",
        "Вид изделия",
        "Вид колбасы",
        "Вид спортивного питания",
        "Вискоза",
        "Выпадение волос",
        "Глицерин",
        "Для кого:",
        "Для улучшения",
        "Для чего:",
        "Дополнительные разделы",
        "Дорожный футляр",
        "Доступність / ефективність",
        "Карбонат кальция",
        "Класс средства",
        "Классификация косметического средства",
        "Комплексы витаминов и минералов",
        "Комплектация зеркалом",
        "Концентрация",
        "Крепость кофе",
        "Культура",
        "Лінії продуктів",
        "Материал внешней стенки",
        "Материал расчески",
        "Минеральное масло",
        "Мягкость",
        "Назначение расчески",
        "Назначение средства для волос",
        "Не щиплет глаза",
        "Основной тип ткани",
        "Основные ингредиенты",
        "Особенность средства",
        "Принцип действия",
        "Отжим",
        "Парабены",
        "Перхоть",
        "Поверхность",
        "Подарочная упаковка",
        "Потреби",
        "Раздел",
        "Різноманітність продукції",
        "Сахарин",
        "Силиконовые компоненты",
        "Синтетические консерванты",
        "Синтетические красители",
        "Синтетические отдушки",
        "Содержание белков в 100 г",
        "Содержание жиров в 100 г",
        "Содержание сахара",
        "Содержание спирта",
        "Содержание углеводов в 100 г",
        "Соединения алюминия",
        "Сорбитол",
        "Состав набора",
        "Состав продукта",
        "Состав чая",
        "Состояние",
        "Срок годности",
        "Стерильные",
        "Тип абразивных частичек",
        "Тип конфет",
        "Тип мяса",
        "Тип насекомых",
        "Тип обработки",
        "Тип продукта пчеловодства",
        "Тип пюре",
        "Тип расчески",
        "Тип румян",
        "Тип средства для пилинга",
        "Тип средства для принятия ванн",
        "Тип средства для умывания",
        "Тип стирки",
        "Тип теней",
        "Тип туалетного мыла",
        "Тип туши",
        "Тип фактора защиты",
        "Тип чая",
        "Тип элементов питания",
        "Триклозан",
        "Фасовка",
        "Форма мыла",
        "Форма щеточки",
        "Фталаты",
        "Фтор",
        "Хлоргексидин",
        "Цвет зубной пасты",
        "Цвет и оттенок краски",
        "Часы",
        "Чувствительность зубов",

        "Вид продукции",
        "Содержание элементов в 1й порции",
        "Содержание порции",
        "Лаурет сульфат аммония",
        "Лаурет сульфат натрия (SLES)"
      ];

      foreach($attr_names as $attr) {
        $attribute = Attribute::where('name->ru', $attr)->first();

        if(!$attribute) {
          $this->error($attr);
          continue;
        }else {
          $this->info($attr . ' - ' . $attribute->type);
        }

        $aps = AttributeProduct::where('attribute_id', $attribute->id)->get();

        foreach($aps as $ap) {
          $product = Product::find($ap->product_id);

          if(!$product) {
            $this->error('No product with id ' . $ap->product_id);
            continue;
          }

          $new_custom_attr = [];

          if($attribute->type === 'string') {
            $new_custom_attr['name'] = $attribute->name;
            $new_custom_attr['value'] = $ap->value_trans;
          }elseif($attribute->type === 'number') {
            $new_custom_attr['name'] = $attribute->name;
            $new_custom_attr['value'] = $ap->value;
          }

          $this->setCustomAttrs($product, $new_custom_attr);
          
          //
          $ap->to_delete = 1;
          $ap->save();
        }

        // Delete attribute
        $attribute->to_delete = 1;
        $attribute->save();
      }
    }
    
    /**
     * setToExtrasTrans
     *
     * @return void
     */
    private function setCustomAttrs($product, $attr) {
      $custom_attrs = $product->customProperties ?? [];

      $custom_attrs[] = $attr;

      $product->setTranslation('extras_trans', 'ru', [
        'custom_attrs' => $custom_attrs
      ]);

      $product->save();     
    }

    
    /**
     * mergeAtrributes
     *
     * @return void
     */
    private function mergeAtrributes() {
      $job_data = [
        [
          'from' => [
            'Вес (г)' => 'string',
            'Вес в упаковке, кг' => 'string',
            'Вес порошка' => 'string',
          ],
          'to_name' => 'Вес',
          'old_type' => 'number'
        ],[
          'from' => [
            'Вид спортивного питания' => 'string'
          ],
          'to_name' => 'Вид',
          'old_type' => 'checkbox',
        ],
        // [
        //   'from' => [
        //     'Тип' => 'checkbox', // 37
        //     'Тип' => 'radio' // 17
        //   ],
        //   'to_name' => 'Тип средства', // 49
        //   'to_type' => 'checkbox',
        //   'old_type' => 'string'
        // ]
        [
          'from' => [
            'Вид и форма выпуска' => 'string',
            'Лекарственная форма' => 'string',
            'Тип лекарственного средства' => 'string'
          ],
          'to_name' => 'Форма выпуска', //5
          'old_type' => 'radio',
        ],[
          'from' => [
            'Вкус зубной пасты'	 => 'string',
            'Вкус, добавки' =>	'checkbox',
            'Вкусовые ингредиенты' =>	'string'
          ],
          'to_name' => 'Вкус',
          'to_type' => 'checkbox',
          'old_type' => 'radio'
        ],[
          'from' => [
            'Возраст ребенка'	=> 'string',
            'Возрастная группа' => 'checkbox',
            'Возрастная категория' =>	'string'
          ],
          'to_name' => 'Возраст',
          'to_type' => 'checkbox',
          'old_type' => 'string'
        ],[
          'from' => [
            'Дозировка' => 'checkbox',
            'Дозировка' => 'string',
            'Дозировка на 1 капсулу:' => 'number'
          ],
          'to_name' => 'Дозировка на 1 порцию (мг):',
          'rename_to' => 'Дозировка на порцию',
          'old_type' => 'string'
        ],[
          'from' => [
            'Количество капсул' =>	'string',
            'Количество капсул:'	=> 'string',
            'Количество порций'	=> 'number',
            'Количество в пачке' =>	'number',
            'Количество в упаковке, шт'	=> 'string',
            // 'Содержание порции'	=> 'string',
            // 'Содержание элементов в 1й порции' =>	'string'
          ],
          'to_name' => 'Количество',
          'rename_to' => 'Количество в упаковке',
          'old_type' => 'number',
        ],[
          'from' => [
            'Консистенция средства' => 'string'
          ],
          'to_name' => 'Консистенция',
          'old_type' => 'string',
        ],[
          'from' => [
            'Основное назначение средства' =>	'checkbox',
            'Ціль'	=> 'checkbox',
            'Целебное назначение' =>	'checkbox'
          ],
          'to_name' => 'Назначение',
          'to_type' => 'checkbox',
          'old_type' => 'radio',
        ],[
          'from' => [],
          'to_name' => 'Особенности',
          'to_type' => 'string',
          'old_type' => 'checkbox',
        ],[
          'from' => [
            'Свойства'	=> 'checkbox'
          ],
          'to_name' => 'Действие',
          'rename_to' => 'Действие средства',
          'old_type' => 'checkbox',
        ],[
          'from' => [
            'По полу'	=> 'radio'
          ],
          'to_name' => 'Пол',
          'old_type' => 'string',
          'to_type' => 'radio',
        ],[
          'from' => [
            'Применение'	=> 'string',
            'Способ применения' =>	'string',
            'Тип применения'	=> 'string',
	          'Проблема волос и кожи головы' =>	'checkbox',
            'Проблема и состояние кожи' => 'checkbox',
            'Проблема кожи' =>	'checkbox'
          ],
          'to_name' => 'Применение препарата',
          'old_type' => 'checkbox',
        ],[
          'from' => [
            'Страна производства активного вещества:'	=> 'string',
            'Страна регистрации бренда'	=> 'string'
          ],
          'to_name' => 'Страна производитель',
          'old_type' => 'radio',
        ],[
          'from' => [
            'Тип гигиенической зубной пасты' =>	'string',
            'Тип лечебно-профилактической зубной пасты' =>	'string'
          ],
          'to_name' => 'Тип зубной пасты',
          'old_type' => 'string',
        ],[
          'from' => [
            'Упаковка средства' =>	'checkbox'
          ],
          'to_name' => 'Упаковка',
          'old_type' => 'checkbox'
        ],[
          'from' => [
            'Упаковка средства' =>	'checkbox'
          ],
          'to_name' => 'Упаковка',
          'old_type' => 'checkbox'
        ]
      ];


      foreach($job_data as $job) {
        // Get target attribute
        $main_attribute = Attribute::where('name->ru', $job['to_name'])->where('type', $job['old_type'])->first();

        if($main_attribute) {
          $this->info('Found');
          $this->transformAttribute($main_attribute, $job);
        }else {
          $this->error('Not Found - ' . $job['to_name'] . ' - ' . $job['old_type']);
        }


        foreach($job['from'] as $attr_name => $attr_type) {
          $attr = Attribute::where('name->ru', $attr_name)->where('type', $attr_type)->first();
          
          if($attr) {
            $this->info('//////////// Found');
            $this->transformAttribute($attr, [
              'to_type' => $job['to_type'] ?? $job['old_type'],
              'old_type' => $attr_type,
            ]);

            $this->mergeSameTypeAttributes($attr, $main_attribute);
            
            $attr->to_delete = 1;
            $attr->save();
          }else {
            $this->error('//////////// Not Found - ' . $attr_name . ' - ' . $attr_type);
          }
        }

      }
    }

        
    /**
     * mergeSameTypeAttributes
     *
     * @param  mixed $from
     * @param  mixed $to
     * @return void
     */
    private function mergeSameTypeAttributes($from, $to) {
      if($from->type !== $to->type) {
        $this->error('Attributes has different types. Closed');
        return;
      }

      AttributeProduct::where('attribute_id', $from->id)->update([
        'attribute_id' => $to->id
      ]);
    }

    /**
     * transformAttribute
     *
     * @param  mixed $attribute
     * @param  mixed $data
     * @return void
     */
    private function transformAttribute($attribute, $data) {
      if(!isset($data['to_type']) && !isset($data['rename_to'])) {
        return;
      }

      if(isset($data['rename_to'])) {
        $attribute->name = $data['rename_to'];
      }

      if(isset($data['to_type']) && $data['to_type'] !== $data['old_type']) {
        $this->changeAttributeType($attribute, $data);
      }
    }
    
    /**
     * changeAttributeType
     *
     * @param  mixed $attribute
     * @param  mixed $data
     * @return void
     */
    private function changeAttributeType($attribute, $data) {
      ///
      /// STRING
      ///
      // STRING -> CHECKBOX or RADIO
      if($data['old_type'] === 'string' && $data['to_type'] === 'checkbox' || $data['to_type'] === 'radio') {
        $aps = AttributeProduct::where('attribute_id', $attribute->id)->get();

        foreach($aps as $ap) {
          $av = $this->findOrCreateAttributeValue($attribute, $ap->value_trans);

          $ap->value_trans = null;
          $ap->attribute_value_id = $av->id;
          $ap->save();
        }
      }

      // STRING -> NUMBER
      if($data['old_type'] === 'string' && $data['to_type'] === 'number') {
        $aps = AttributeProduct::where('attribute_id', $attribute->id)->get();

        foreach($aps as $ap) {
          $ap->value = $this->getClearNumberValue($ap->value_trans);
          $ap->value_trans = null;
          $ap->save();
        }
      }



      ///
      /// NUMBER
      ///
      // NUMBER -> STRING
      if($data['old_type'] === 'number' && $data['to_type'] === 'string') {
        $aps = AttributeProduct::where('attribute_id', $attribute->id)->get();

        foreach($aps as $ap) {
          $ap->setTranslation('value_trans', $ap->value, 'ru');
          $ap->value = null;
          $ap->save();
        }
      }



      ///
      /// CHECKBOX
      ///
      // CHECKBOX -> RADIO
      if($data['old_type'] === 'checkbox' && $data['to_type'] === 'radio') {

      }

      // CHECKBOX -> STRING
      if($data['old_type'] === 'checkbox' && $data['to_type'] === 'string') {
        $aps = AttributeProduct::where('attribute_id', $attribute->id)->get();

        foreach($aps as $ap) {
          $value = AttributeValue::find($ap->attribute_value_id);

          if(!$value) {
            $this->error('value not found ' . $ap->attribute_value_id . ' in ap ' . $ap->id);
            continue;
          }

          // Set new value to ap
          $ap->attribute_value_id = null;
          $ap->setTranslation('value_trans', $value->value,'ru');
          $ap->save();

          // Delete value
          $value->to_delete = 1;
          $value->save();
        }
      }

      //
      $attribute->type = $data['to_type'];
      $attribute->save();
    }

    
    /**
     * findOrCreateAttributeValue
     *
     * @param  mixed $attribute
     * @param  mixed $value
     * @return void
     */
    private function findOrCreateAttributeValue($attribute, $value) {
      $av = AttributeValue::where('value->ru', $value)->where('attribute_id', $attribute->id)->first();

      if($av) {
        return $av;
      }

      // 
      $av = new AttributeValue;
      $av->attribute_id = $attribute->id;
      $av->value = $value;
      $av->save();

      return $av;
    }


    /**
     * getClearNumberValue
     *
     * @param  mixed $value
     * @return void
     */
    public function getClearNumberValue($value) {
      if(empty($value)) {
        return null;
      }

      $clear = preg_replace('/[^0-9]/', '', $value);
      
      $this->info((double)$clear);
      return (double)$clear;
    }

}
