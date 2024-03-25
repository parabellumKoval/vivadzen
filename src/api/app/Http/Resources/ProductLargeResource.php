<?php

namespace App\Http\Resources;

class ProductLargeResource extends \Backpack\Store\app\Http\Resources\BaseResource
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
        'price' => $this->price,
        'code' => $this->code,
        'oldPrice' => $this->old_price,
        'rating' => $this->rating,
        'reviews_rating_detailes' => $this->reviewsRatingDetailes,
        'images' => $this->images,
        'content' => $this->content,
        'inStock' => $this->in_stock,
        'categories' => $this->categories && $this->categories->count()? 
          self::$resources['category']['tiny']::collection($this->categories): 
            null,
        'attrs' => $this->attributes,
        'seo' => $this->seoArray
      ];
    }
}
