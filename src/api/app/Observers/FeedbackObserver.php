<?php

namespace App\Observers;

use App\Models\User;
use Backpack\Store\app\Models\Order;
use Illuminate\Database\Eloquent\Builder;
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
            $result = $this->sendLandingPromoCode($feedback);
            $this->savePromoSubscribeResult($feedback, $result);
        }
    }

    protected function sendLandingPromoCode(Feedback $feedback): array
    {
        $email = trim((string) $feedback->email);

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return [
                'status' => 'invalid_email',
                'promo_sent' => false,
            ];
        }

        if ($this->emailHasOrderHistory($email)) {
            return [
                'status' => 'existing_customer',
                'promo_sent' => false,
                'message_key' => 'success_existing_customer',
            ];
        }

        $promoCode = trim((string) config('landing.promo_subscribe.promo_code'));

        if ($promoCode === '') {
            return [
                'status' => 'promo_code_missing',
                'promo_sent' => false,
            ];
        }

        $ctaUrl = trim((string) config('landing.promo_subscribe.cta_url'));
        $ctaUrl = $ctaUrl !== '' ? $ctaUrl : null;

        Mail::to($email)->queue(new LandingPromoCode($promoCode, $ctaUrl));

        return [
            'status' => 'sent',
            'promo_sent' => true,
            'message_key' => 'success',
        ];
    }

    protected function emailHasOrderHistory(string $email): bool
    {
        $email = mb_strtolower(trim($email));

        if ($email === '') {
            return false;
        }

        return Order::query()
            ->where(function (Builder $query) use ($email): void {
                $this->applyOrderEmailFilter($query, $email);

                $query->orWhereHasMorph('orderable', [User::class], function (Builder $orderableQuery) use ($email): void {
                    $orderableQuery->whereRaw('LOWER(email) = ?', [$email]);
                });
            })
            ->exists();
    }

    protected function applyOrderEmailFilter(Builder $query, string $email): void
    {
        $driver = $query->getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $query->whereRaw("LOWER(info->'user'->>'email') = ?", [$email]);
            return;
        }

        if ($driver === 'sqlite') {
            $query->whereRaw("LOWER(json_extract(info, '$.user.email')) = ?", [$email]);
            return;
        }

        if ($driver === 'mysql') {
            $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(info, '$.user.email'))) = ?", [$email]);
            return;
        }

        $query->where('info->user->email', $email);
    }

    protected function savePromoSubscribeResult(Feedback $feedback, array $result): void
    {
        $extras = $this->normalizeExtras($feedback->extras);
        $promoSubscribe = $this->normalizeExtras($extras['promo_subscribe'] ?? []);

        $promoSubscribe = array_merge($promoSubscribe, [
            'status' => $result['status'] ?? null,
            'promo_sent' => (bool) ($result['promo_sent'] ?? false),
        ]);

        if (!empty($result['message_key'])) {
            $promoSubscribe['message_key'] = (string) $result['message_key'];
        }

        $extras['promo_subscribe'] = $promoSubscribe;

        $feedback->extras = $extras;
        $feedback->saveQuietly();
    }

    protected function normalizeExtras(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

}
