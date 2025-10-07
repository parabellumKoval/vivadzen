<?php

namespace App\Http\Models\Traits;

// REVIEWS
use Backpack\Reviews\app\Traits\Reviewable;

trait ProductModel {
  use Reviewable;
  
  // public function getFaqAttribute() {
  //   return !empty($this->fieldsDecoded->faq)? json_decode($this->fieldsDecoded->faq): null;
  // }
  // public function reviews(){
  //   $model = config('backpack.reviews.review_model', 'Backpack\Reviews\app\Models\Review');
  //   return $this->morphMany($model, 'reviewable');
  // }
}