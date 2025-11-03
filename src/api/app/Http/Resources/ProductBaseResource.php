<?php

namespace App\Http\Resources;

class ProductBaseResource extends \Backpack\Store\app\Http\Resources\BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
      $repr =  $this->modifications->first() ?? $this;

      return [
        'id' => $this->product_id,
        'name' => $this->name,
        'slug' => $repr->slug,
        'price' => $repr->price,
        'oldPrice' => $repr->old_price,
        'currency' => $this->currency,
        'rating' => $this->rating,
        'reviews' => $this->reviews,
        'ratings' => $this->ratings,
        'image' => $this->getFirstImageForApi(),
        'inStock' => $this->in_stock,
      ];
    }
}
