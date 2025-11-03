<?php

namespace App\Mail;

use Backpack\Profile\app\Models\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReferralSponsorNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected Profile $sponsor,
        protected Profile $referral
    ) {
        app()->setLocale($this->determineLocale());
    }

    public function build(): self
    {
        return $this->subject(__('mail.referral.new_sponsor_subject'))
            ->markdown('mail.referral_sponsor_notification')
            ->with([
                'sponsor' => $this->sponsor,
                'referral' => $this->referral,
                'details' => $this->details(),
                'ctaUrl' => $this->ctaUrl(),
                'sponsorName' => $this->displayName($this->sponsor),
                'referralName' => $this->displayName($this->referral),
            ]);
    }

    protected function determineLocale(): string
    {
        return $this->sponsor->locale
            ?? $this->sponsor->user?->locale
            ?? config('app.locale', 'uk');
    }

    protected function details(): array
    {
        $lines = [];

        $name = trim(
            implode(' ', array_filter([
                $this->referral->first_name ?? $this->referral->firstname ?? null,
                $this->referral->last_name ?? $this->referral->lastname ?? null,
            ]))
        );

        if ($name !== '') {
            $lines[] = __('mail.referral.labels.name') . ': <b>' . e($name) . '</b>';
        }

        $email = $this->referral->email ?? $this->referral->user?->email ?? null;

        if ($email) {
            $lines[] = __('mail.referral.labels.email') . ': <b>' . e($email) . '</b>';
        }

        $phone = $this->referral->phone;

        if ($phone) {
            $lines[] = __('mail.referral.labels.phone') . ': <b>' . e($phone) . '</b>';
        }

        if ($this->referral->created_at) {
            $lines[] = __('mail.referral.labels.registered_at') . ': <b>' . e($this->referral->created_at->format('d.m.Y H:i')) . '</b>';
        }

        if ($this->referral->referral_code) {
            $lines[] = __('mail.referral.labels.referral_code') . ': <b>' . e($this->referral->referral_code) . '</b>';
        }

        return $lines;
    }

    protected function ctaUrl(): string
    {
        return url('/');
    }

    protected function displayName(Profile $profile): string
    {
        $name = trim(
            implode(' ', array_filter([
                $profile->first_name ?? $profile->firstname ?? null,
                $profile->last_name ?? $profile->lastname ?? null,
            ]))
        );

        if ($name !== '') {
            return $name;
        }

        return $profile->user?->name ?? '';
    }
}
