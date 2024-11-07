<?php

namespace App\Http\Resources;
use \Backpack\Store\app\Http\Resources\BaseResource;

use App\Http\Resources\CategoryParentResource;

class ProductLargeResource extends BaseResource
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
        'code' => $this->simpleCode,
        'price' => $this->simplePrice,
        'oldPrice' => $this->simpleOldPrice,
        'inStock' => $this->simpleInStock,
        'rating' => $this->rating,
        'reviews_rating_detailes' => $this->reviewsRatingDetailes,
        'images' => $this->images,
        'content' => $this->content,
        'categories' => $this->categories && $this->categories->count()?
          CategoryParentResource::collection($this->categories):
            null,
        'no_medicine' => $this->category? $this->category->noMedicine: 1,
        'modifications' => $this->modifications && $this->modifications->count()? 
          self::$resources['product']['tiny']::collection($this->modifications): 
            null,
        'attrs' => $this->properties,
        'specs' => $this->specs,
        'seo' => $this->seoArray,
        'brand' => $this->brand? new BrandProductResource($this->brand): null
      ];
    }
}
