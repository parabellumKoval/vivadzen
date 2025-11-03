<?php

namespace ParabellumKoval\BackpackImages\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ImageCollectionCast implements CastsAttributes
{
    public function __construct(protected ?string $attribute = null)
    {
    }

    public function get($model, string $key, $value, array $attributes)
    {
        $attribute = $this->attribute ?? $key;

        if (!method_exists($model, 'normalizeImagesForAttribute')) {
            return is_array($value) ? $value : [];
        }

        return $model->normalizeImagesForAttribute($attribute, $value);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        $attribute = $this->attribute ?? $key;

        if (!method_exists($model, 'prepareImageCollectionValueForStorage')) {
            return [$key => $value];
        }

        return [
            $key => $model->prepareImageCollectionValueForStorage($attribute, $value),
        ];
    }
}
