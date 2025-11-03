<?php

namespace App\Mail;

use Backpack\Profile\app\Models\Reward;
use Backpack\Profile\app\Models\RewardEvent;
use Backpack\Profile\app\Models\WalletLedger;
use Backpack\Profile\app\Support\TriggerLabels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RewardLedgerEntryNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected array $triggerMeta;
    protected array $payload;

    public function __construct(
        protected Authenticatable $recipient,
        protected RewardEvent $event,
        protected Reward $reward,
        protected WalletLedger $ledger,
        protected ?string $balance
    ) {
        $this->triggerMeta = TriggerLabels::resolve($event->trigger);
        $this->payload = is_array($event->payload) ? $event->payload : [];

        app()->setLocale($this->determineLocale());
    }

    public function build(): self
    {
        $direction = $this->ledger->type === 'debit' ? 'debit' : 'credit';

        $subjectKey = $direction === 'credit'
            ? 'mail.reward.credit_subject'
            : 'mail.reward.debit_subject';

        $amountFormatted = $this->formatAmount(abs((float) $this->ledger->amount), (string) $this->ledger->currency);

        return $this->subject(__($subjectKey))
            ->markdown('mail.reward_ledger_entry')
            ->with([
                'recipient' => $this->recipient,
                'event' => $this->event,
                'reward' => $this->reward,
                'ledger' => $this->ledger,
                'balance' => $this->formattedBalance(),
                'direction' => $direction,
                'triggerMeta' => $this->triggerMeta,
                'amountLabel' => $amountFormatted,
                'details' => $this->details(),
                'isReversal' => (bool) ($this->triggerMeta['reversal'] ?? false),
                'intro' => __($this->introKey($direction), ['amount' => $amountFormatted]),
                'ctaUrl' => $this->ctaUrl(),
            ]);
    }

    protected function determineLocale(): string
    {
        $profile = method_exists($this->recipient, 'profile') ? $this->recipient->profile : null;

        return $profile->locale
            ?? $this->recipient->locale
            ?? config('app.locale', 'uk');
    }

    protected function formattedBalance(): ?string
    {
        if ($this->balance === null) {
            return null;
        }

        return $this->formatAmount((float) $this->balance, (string) $this->ledger->currency);
    }

    protected function details(): array
    {
        $lines = [];

        $lines[] = __('mail.reward.labels.trigger') . ': <b>' . e($this->triggerMeta['label'] ?? $this->event->trigger) . '</b>';
        $lines[] = __('mail.reward.labels.trigger_key') . ': <b>' . e($this->event->trigger) . '</b>';

        if (!empty($this->triggerMeta['description'])) {
            $lines[] = __('mail.reward.labels.description') . ': ' . e($this->triggerMeta['description']);
        }

        if ($this->event->external_id) {
            $lines[] = __('mail.reward.labels.external_id') . ': <b>' . e($this->event->external_id) . '</b>';
        }

        if ($this->reward->beneficiary_type) {
            $lines[] = __('mail.reward.labels.beneficiary_type') . ': <b>' . e($this->beneficiaryTypeLabel()) . '</b>';
        }

        if ($this->reward->level !== null) {
            $lines[] = __('mail.reward.labels.level') . ': <b>' . e((string) $this->reward->level) . '</b>';
        }

        $lines = array_merge($lines, $this->payloadLines());

        $lines[] = __('mail.reward.labels.reference_type') . ': <b>' . e($this->ledger->reference_type ?? '—') . '</b>';
        if ($this->ledger->reference_id) {
            $lines[] = __('mail.reward.labels.reference_id') . ': <b>' . e($this->ledger->reference_id) . '</b>';
        }

        if ($this->balance !== null) {
            $lines[] = __('mail.reward.labels.balance') . ': <b>' . e($this->formattedBalance()) . '</b>';
        }

        return array_filter($lines);
    }

    protected function payloadLines(): array
    {
        $lines = [];

        if (isset($this->payload['order_id'])) {
            $lines[] = __('mail.reward.labels.order_id') . ': <b>' . e((string) $this->payload['order_id']) . '</b>';
        }

        if (isset($this->payload['total'])) {
            $currency = (string) ($this->payload['currency'] ?? $this->ledger->currency);
            $lines[] = __('mail.reward.labels.order_total') . ': <b>' . e($this->formatAmount((float) $this->payload['total'], $currency)) . '</b>';
        }

        if (isset($this->payload['review_id'])) {
            $lines[] = __('mail.reward.labels.review_id') . ': <b>' . e((string) $this->payload['review_id']) . '</b>';
        }

        if (isset($this->payload['rating'])) {
            $lines[] = __('mail.reward.labels.rating') . ': <b>' . e((string) $this->payload['rating']) . '</b>';
        }

        return $lines;
    }

    protected function beneficiaryTypeLabel(): string
    {
        $key = 'mail.reward.beneficiaries.' . $this->reward->beneficiary_type;
        $label = __($key);

        return $label === $key ? ucfirst((string) $this->reward->beneficiary_type) : $label;
    }

    protected function formatAmount(float $amount, string $currency): string
    {
        return number_format($amount, 2, '.', ' ') . ' ' . $currency;
    }

    protected function introKey(string $direction): string
    {
        return $direction === 'credit'
            ? 'mail.reward.intro.credit'
            : 'mail.reward.intro.debit';
    }

    protected function ctaUrl(): string
    {
        return url('/');
    }
}
