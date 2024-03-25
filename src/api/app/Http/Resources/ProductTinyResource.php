<?php

namespace App\Http\Resources;

class ProductTinyResource extends \Backpack\Store\app\Http\Resources\BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
      return [
        'id' => $this->id,
        'name' => $this->name,
        'short_name' => $this->short_name,
        'slug' => $this->slug,
        'price' => $this->price,
        'oldPrice' => $this->old_price,
        'inStock' => $this->in_stock,
        'attrs' => $this->attrs && $this->attrs->count()? 
          self::$resources['attribute']['small']::collection($this->attrs): 
            null,
      ];
    }
}
