<?php

namespace App\Http\Resources;

class ProductMediumResource extends \Backpack\Store\app\Http\Resources\BaseResource
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
        'slug' => $this->slug,
        'price' => $this->simplePrice,
        'oldPrice' => $this->simpleOldPrice,
        'image' => $this->image,
        // 'content' => $this->content,
        'inStock' => $this->simpleInStock,
        'category' => $this->category,
      ];
    }
}
