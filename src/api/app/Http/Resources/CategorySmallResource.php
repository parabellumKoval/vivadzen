<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class CategorySmallResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
      $children = $this->children()->orderBy('lft')->get();

      return [
        'id' => $this->id,
        'name' => $this->name,
        'slug' => $this->slug,
        'image' => $this->image,
        'children' => self::collection($children),
        'is_hit' => $this->extras['is_hit'] ?? 0
      ];
    }
}
