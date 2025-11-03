<?php

namespace App\Mail;

use Backpack\Profile\app\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

abstract class WithdrawalNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected ?string $recipientLocale = null;

    public function __construct(
        protected WithdrawalRequest $withdrawal
    ) {
        $this->withdrawal->loadMissing('user.profile');
        $this->recipientLocale = $this->determineLocale();

        app()->setLocale($this->recipientLocale);
    }

    abstract protected function subjectKey(): string;

    abstract protected function titleKey(): string;

    abstract protected function viewName(): string;

    protected function titleParameters(): array
    {
        return ['id' => $this->withdrawal->id];
    }

    public function build(): self
    {
        return $this->subject(__($this->subjectKey(), $this->titleParameters()))
            ->markdown($this->viewName())
            ->with([
                'withdrawal' => $this->withdrawal,
                'details' => $this->details(),
                'title' => __($this->titleKey(), $this->titleParameters()),
                'ctaUrl' => $this->ctaUrl(),
            ]);
    }

    protected function determineLocale(): string
    {
        $user = $this->withdrawal->user;

        if ($user && method_exists($user, 'profile')) {
            $profile = $user->profile;
            if ($profile && $profile->locale) {
                return $profile->locale;
            }
        }

        if ($user && $user->locale) {
            return $user->locale;
        }

        return config('app.locale', 'uk');
    }

    protected function details(): array
    {
        $lines = [];

        $lines[] = __('mail.withdrawal.labels.amount') . ': <b>' . e($this->formatAmount($this->withdrawal->amount, $this->withdrawal->currency)) . '</b>';

        if ($this->withdrawal->wallet_amount !== null && $this->withdrawal->wallet_currency) {
            $lines[] = __('mail.withdrawal.labels.wallet_amount') . ': <b>' . e($this->formatAmount($this->withdrawal->wallet_amount, $this->withdrawal->wallet_currency)) . '</b>';
        }

        $statusLabel = __(
            'mail.withdrawal.statuses.' . $this->withdrawal->status,
            [],
            $this->recipientLocale
        );

        if ($statusLabel === 'mail.withdrawal.statuses.' . $this->withdrawal->status) {
            $statusLabel = ucfirst($this->withdrawal->status);
        }

        $lines[] = __('mail.withdrawal.labels.status') . ': <b>' . e($statusLabel) . '</b>';

        if ($this->withdrawal->payout_method) {
            $lines[] = __('mail.withdrawal.labels.method') . ': <b>' . e($this->withdrawal->payout_method) . '</b>';
        }

        if ($this->withdrawal->approved_at) {
            $lines[] = __('mail.withdrawal.labels.approved_at') . ': <b>' . e($this->withdrawal->approved_at->format('d.m.Y H:i')) . '</b>';
        }

        if ($this->withdrawal->paid_at) {
            $lines[] = __('mail.withdrawal.labels.paid_at') . ': <b>' . e($this->withdrawal->paid_at->format('d.m.Y H:i')) . '</b>';
        }

        $lines[] = __('mail.withdrawal.labels.requested_at') . ': <b>' . e($this->withdrawal->created_at?->format('d.m.Y H:i') ?? '') . '</b>';

        if ($this->withdrawal->fx_rate) {
            $lines[] = __('mail.withdrawal.labels.fx_rate') . ': <b>' . e($this->withdrawal->fx_rate) . '</b>';
        }

        if (!empty($this->withdrawal->payout_details) && is_array($this->withdrawal->payout_details)) {
            foreach ($this->withdrawal->payout_details as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                $labelKey = 'mail.withdrawal.labels.detail_' . $key;
                $label = __($labelKey);
                if ($label === $labelKey) {
                    $label = ucfirst(str_replace('_', ' ', (string) $key));
                }

                $lines[] = $label . ': <b>' . e((string) $value) . '</b>';
            }
        }

        return $lines;
    }

    protected function formatAmount(float|string $amount, string $currency): string
    {
        return number_format((float) $amount, 2, '.', ' ') . ' ' . $currency;
    }

    protected function ctaUrl(): string
    {
        return url('/');
    }
}
