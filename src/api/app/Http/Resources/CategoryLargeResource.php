<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\CategoryParentResource;

class CategoryLargeResource extends JsonResource
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
        'content' => $this->content,
        'excerpt' => $this->excerpt,
        'extras' => $this->extras,
        'images' => $this->images,
        'parent' => $this->parent? new CategoryParentResource($this->parent): null,
        'seo' => $this->seoToArray
      ];
    }
}
