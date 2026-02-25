<?php

namespace App\Http\Resources;

use App\Services\AgeVerification\AgeVerificationService;

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
      $ageVerificationService = app(AgeVerificationService::class);
      $requiresAgeVerification = $ageVerificationService->productRequiresVerification($this->resource);

      return [
        'id' => $this->product_id ?? $this->id,
        'name' => $this->name,
        'shortName' => $this->short_name,
        'slug' => $this->slug,
        'price' => $this->price,
        'oldPrice' => $this->old_price,
        'currency' => $this->currency,
        'rating' => $this->rating,
        'image' => $this->effective()->getFirstImageForApi(),
        'inStock' => $this->in_stock,
        'store_only' => (bool) ($this->store_only ?? false),
        'storeOnly' => (bool) ($this->store_only ?? false),
        'requires_age_verification' => (bool) $requiresAgeVerification,
        'requiresAgeVerification' => (bool) $requiresAgeVerification,
        'external' => $this->external ?? 0,
        'amount' => $this->amount
      ];
    }
}
