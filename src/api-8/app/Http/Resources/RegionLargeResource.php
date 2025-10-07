<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionLargeResource extends JsonResource
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
        'name' => $this->category->name . ' ' . $this->name,
        'slug' => $this->category->slug,
        'content' => $this->content,
        'extras' => $this->extras,
        'seo' => $this->seoToArray,
        'children' => null,
        'region_slug' => $this->slug,
        'catalog_type' => 'region'
      ];
    }
}
