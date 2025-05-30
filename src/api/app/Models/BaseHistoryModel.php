<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

abstract class BaseHistoryModel extends Model
{
    use CrudTrait;

    protected $guarded = ['id'];
    protected $fakeColumns = ['extras'];
    protected $casts = [
        'extras' => 'array',
    ];

    const TYPES = [
        'Backpack\Store\app\Models\Product' => 'Товар',
        'Backpack\Store\app\Models\Category' => 'Категория',
        'Backpack\Store\app\Models\Attribute' => 'Атрибут',
        'Backpack\Store\app\Models\AttributeValue' => 'Значение атрибута',
        'Backpack\Store\app\Models\AttributeProduct' => 'Атрибут товара',
        'Backpack\Store\app\Models\Brand' => 'Бренд',
    ];

    const SLUGS = [
        'Backpack\Store\app\Models\Product' => 'product',
        'Backpack\Store\app\Models\Category' => 'category',
        'Backpack\Store\app\Models\Attribute' => 'attribute',
        'Backpack\Store\app\Models\AttributeValue' => [
            'Backpack\Store\app\Models\Attribute' => 'attribute',
        ],
        'Backpack\Store\app\Models\AttributeProduct' => [
            'Backpack\Store\app\Models\Product' => 'product',
            'Backpack\Store\app\Models\Attribute' => 'attribute',
            'identifiableAttribute' => 'value_trans'
        ],
        'Backpack\Store\app\Models\Brand' => 'brand',
    ];

    const FIELDS = [
        'custom_props' => 'Индивидуальные характеристики',
        'brand' => [
            'name' => 'Бренд',
            'identifiableAttribute' => 'name',
            'getter' => 'brand',
        ],
        'category' => [
            'name' => 'Категория',
            'identifiableAttribute' => 'name',
            'getter' => 'category',
        ],
        'properties' => 'Атрибуты / Индивидуальные характеристики / Особенности',
        'content' => 'Описание',
        'name' => 'Название',
    ];

    /**
     * Method createItem
     *
     * @param $item $item [explicite description]
     * @param array|null $extras
     * @return static
     */
    public static function createItem($item, $extras = null) {
        $last = static::latest('id')->first();
        $morphField = static::getMorphField();

        $data = [
            $morphField . '_type' => $item->getMorphClass() ?? get_class($item),
            $morphField . '_id' => $item->id,
            'status' => 'pending',
            'extras' => $extras && is_array($extras) ? $extras : null,
        ];

        if (
            $last &&
            $last->{$morphField . '_type'} === $data[$morphField . '_type'] &&
            $last->{$morphField . '_id'} === $data[$morphField . '_id']
        ) {
            // Update last record if it has the same morph type and id
            $last->update($data);
            $model = $last;
        } else {
            // Create new record if last record has different morph type or id
            $model = static::create($data);
        }

        return $model;
    }

    /**
     * Method updateStatus
     *
     * @param string $status
     * @param string|array|null $message
     * @return bool
     */
    public function updateStatus($status, string|array $message = null) {
        try {
            $this->status = $status;
            
            if ($message !== null) {
                $old_extras = $this->extras ?? [];

                if(is_array($message)) {
                    $old_extras = array_merge($old_extras, $message);
                }else if (is_string($message)) {
                    $old_extras['message'] = $message;
                }

                $this->extras = $old_extras;
            }

            $this->save();
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    /**
     * Method getMessageAttribute
     *
     * @return string|null
     */
    public function getMessageAttribute() {
        return $this->extras['message'] ?? null;
    }

    /**
     * Method getMessageAdminAttribute
     *
     * @return string
     */
    public function getMessageAdminAttribute() {
        return $this->message ?? '–';
    }

    /**
     * getStatusHtmlAttribute
     *
     * @return string
     */
    public function getStatusHtmlAttribute() {
        switch($this->status) {
            case 'done':
                $color = 'green';
                break;
            case 'pending':
                $color = 'gray';
                break;
            case 'error':
                $color = 'red';
                break;
            default:
                $color = 'black';
        }

        $translated_status = __('status.common.' . $this->status);

        return "<span style='color: {$color}'>{$translated_status}</span>";
    }

    /**
     * getExtrasToArrayAttribute
     *
     * @return array|null
     */
    public function getExtrasToArrayAttribute() {
        return !empty($this->extras) ? json_decode($this->extras) : null;
    }
    
    /**
     * Method getFieldAttribute
     *
     * @return void
     */
    public function getFieldAttribute()
    {
        return isset($this->extras['field']) && !empty($this->extras['field']) ? $this->extras['field'] : null;
    }

    /**
     * Method getFieldNameAttribute
     *
     * @return void
     */
    public function getFieldNameAttribute()
    {
        $field = $this->field;
        return $field ? self::FIELDS[$field] : null;
    }

    
    /**
     * Method getFieldRelationOrName
     *
     * @param $field $field [explicite description]
     *
     * @return void
     */
    public function getFieldRelationOrName($model, $field) {
        if(isset(self::FIELDS[$field]) && is_array(self::FIELDS[$field])) {
            $relation = self::FIELDS[$field]['getter'] ?? null;
            $relation_model_title = self::FIELDS[$field]['name'] ?? null;
            $relation_field = self::FIELDS[$field]['identifiableAttribute'] ?? null;

            $model_relation = $model->{$relation} ?? null;
            if($model_relation) {
                $relation_model_name = $model_relation->{$relation_field};
                // dd(get_class($model), $relation, $relation_field, $relation_model_name, $this->fieldName);
                return "<span>{$relation_model_title}: <b>{$relation_model_name}</b></span>";
            }else {
                return "<span class='text-muted'>Модель отсутсвует</span>";
            }
        }else {
            return $this->fieldName;
        }
    }

    public function getTargetLinkAdminAttribute()
    {
        $morphField = static::getMorphField();
        $morphType = $this->{$morphField . '_type'};
        $target = $this->{$morphField};
        
        if(!$target) {
            return 'Удалено';
        }
        
        $slug = self::SLUGS[$morphType] ?? null;
        $identifiableAttribute = is_array($slug) && isset($slug['identifiableAttribute']) ? $slug['identifiableAttribute'] : $target->identifiableAttribute();
        $title = $target->{$identifiableAttribute} ?? "id: {$target->id}";

        if (is_array($slug)) {
            $breadcrumbs = [];
            $current = $target;

            foreach (array_reverse($slug) as $parent_type => $parent_slug) {
                if ($parent_type === 'identifiableAttribute') {
                    continue;
                }

                if (method_exists($current, $parent_slug)) {
                    $current_item = $current->{$parent_slug};
                } else {
                    continue;
                }

                $parent_title = $current_item->{$current_item->identifiableAttribute()} ?? "id: {$current_item->id}";
                $breadcrumbs[] = "<a href='/admin/{$parent_slug}/{$current_item->id}/edit'>{$parent_title}</a>";
            }

            $breadcrumbs = array_reverse($breadcrumbs);
            return implode(" -> ", $breadcrumbs) . " -> " . $title;
        } elseif ($slug) {
            $fieldInfo = $this->field ? " -> " . $this->getFieldRelationOrName($target, $this->field) : '';
            return "<a href='/admin/{$slug}/{$target->id}/edit'>{$title}</a>" . $fieldInfo;
        } else {
            return $title;
        }
    }

    /**
     * Get the morph field name (e.g., 'generatable' or 'translatable')
     *
     * @return string
     */
    abstract protected static function getMorphField(): string;
}