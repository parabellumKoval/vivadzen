<?php

namespace App\Mail;

use App\Mail\Concerns\InteractsWithRegionalContext;
use App\Mail\Concerns\RoutesToEmailQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LandingPromoCode extends Mailable
{
    use Queueable, SerializesModels;
    use InteractsWithRegionalContext;
    use RoutesToEmailQueue;

    public string $promoCode;
    public ?string $ctaUrl;

    public function __construct(string $promoCode, ?string $ctaUrl = null)
    {
        $this->promoCode = $promoCode;
        $this->ctaUrl = $ctaUrl;
        $this->routeToEmailQueue();
        $this->initializeRegionalContext();
    }

    public function build()
    {
        return $this->subject(__('mail.landing_promo.subject'))
            ->markdown('mail.landing_promo_code')
            ->with($this->regionalViewData([
                'promoCode' => $this->promoCode,
                'ctaUrl' => $this->ctaUrl,
            ]));
    }
}
