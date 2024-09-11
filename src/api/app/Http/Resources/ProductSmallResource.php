<?php

namespace App\Http\Resources;

class ProductSmallResource extends \Backpack\Store\app\Http\Resources\BaseResource
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
        'rating' => $this->rating,
        'reviews_rating_detailes' => $this->reviewsRatingDetailes,
        'images' => $this->getImages(2),
        'inStock' => $this->simpleInStock,
      ];
    }
}
