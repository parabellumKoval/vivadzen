<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewMediumResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
      $clear_extras = $this->extras;
      unset($clear_extras['owner']);

      return [
        'id' => $this->id,
        'rating' => $this->rating,
        'likes' => $this->likes? $this->likes: 0,
        'dislikes' => $this->dislikes? $this->dislikes: 0,
        'text' => $this->text,
        'author' => $this->ownerModelOrInfo,
        'extras' => $clear_extras,
        'children' => self::collection($this->children()->moderated()->get()),
        'created_at' => $this->created_at
      ];
    }
}
