<?php

namespace App\Http\Resources;

class ProductCartResource extends \Backpack\Store\app\Http\Resources\BaseResource
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
        'code' => $this->code,
        'short_name' => $this->short_name,
        'price' => $this->price,
        'old_price' => $this->oldPrice,
        'image' => $this->getFirstImageForApi(),
        'amount' => $this->amount
      ];
    }
}
