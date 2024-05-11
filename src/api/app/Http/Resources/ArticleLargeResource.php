<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleLargeResource extends JsonResource
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
		    'title' => $this->title,
		    'slug' => $this->slug,
		    'content' => $this->content,
		    'image' => '/images/blog/' . $this->image,
		    'time' => $this->extras['time'] ?? null,
        'seo' => $this->seo
      ];
    }
}
