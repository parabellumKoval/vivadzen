<?php

namespace App\Http\Resources;

use App\Services\AgeVerification\AgeVerificationService;

class ProductLargeResource extends \Backpack\Store\app\Http\Resources\ProductLargeResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = parent::toArray($request);
        $ageVerificationService = app(AgeVerificationService::class);
        $requiresAgeVerification = $ageVerificationService->isEnabled()
            && $ageVerificationService->productRequiresVerification($this->resource);

        $data['requires_age_verification'] = (bool) $requiresAgeVerification;
        $data['requiresAgeVerification'] = (bool) $requiresAgeVerification;

        return $data;
    }
}
