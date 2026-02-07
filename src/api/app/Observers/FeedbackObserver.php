<?php

namespace App\Observers;

use Illuminate\Support\Facades\Mail;

use Backpack\Feedback\app\Models\Feedback;

use App\Mail\Buy1ClickCreatedAdmin;
use App\Mail\LandingPromoCode;
use App\Support\AdminNotificationResolver;

class FeedbackObserver
{
    /**
     * Handle the Feedback "created" event.
     *
     * @param  Backpack\Feedback\app\Models\Feedback  $feedback
     * @return void
     */
    public function created(Feedback $feedback)
    {
        $type = strtolower(trim((string) $feedback->type));

        $adminNotificationTypes = [
            '1_click_buy',
            'landing_sample_set',
        ];

        if (in_array($type, $adminNotificationTypes, true)) {
            $recipients = AdminNotificationResolver::resolve();

            if (!empty($recipients)) {
                Mail::to($recipients)->queue(new Buy1ClickCreatedAdmin($feedback));
            }
        }

        $promoType = strtolower(trim((string) config('landing.promo_subscribe.feedback_type')));
        if ($promoType !== '' && $type === $promoType) {
            $this->sendLandingPromoCode($feedback);
        }
    }

    protected function sendLandingPromoCode(Feedback $feedback): void
    {
        $email = trim((string) $feedback->email);

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return;
        }

        $promoCode = trim((string) config('landing.promo_subscribe.promo_code'));

        if ($promoCode === '') {
            return;
        }

        $ctaUrl = trim((string) config('landing.promo_subscribe.cta_url'));
        $ctaUrl = $ctaUrl !== '' ? $ctaUrl : null;

        Mail::to($email)->queue(new LandingPromoCode($promoCode, $ctaUrl));
    }

}
