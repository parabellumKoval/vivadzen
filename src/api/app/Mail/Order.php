<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class Order extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $products = [];
    public $payment = [];
    public $delivery = [];
    public $customer = [];
    public $summary = [];
    public $pricing = [];
    public $adjustments = [];
    public $invoice = [];
    public $currency = '';

    /**
     * Backwards compatibility properties.
     */
    public $user = [];
    public $common = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
        app()->setLocale('uk');
    }

    /**
     * Prepare all view data for the email.
     *
     * @return void
     */
    public function prepareData(): void
    {
        $this->currency = $this->determineCurrency();
        $this->summary = $this->buildSummaryLines();
        $this->pricing = $this->buildPricingLines();
        $this->adjustments = $this->buildAdjustmentLines();
        $this->customer = $this->buildCustomerLines();
        $this->delivery = $this->buildDeliveryLines();
        $this->payment = $this->buildPaymentLines();
        $this->products = $this->formatProducts();

        // Expose legacy aliases for existing templates.
        $this->user = $this->customer;
        $this->common = $this->summary;
    }

    protected function determineCurrency(): string
    {
        $currency = (string) ($this->order->currency_code ?? '');

        if (! $currency) {
            $country = $this->order->country_code ?? null;

            if ($country) {
                try {
                    $currency = (string) \Store::countryCurrency($country);
                } catch (\Throwable $exception) {
                    $currency = '';
                }
            }
        }

        if (! $currency) {
            $currency = (string) config('dress.store.currency.default', config('app.currency', 'UAH'));
        }

        return $currency ?: 'UAH';
    }

    protected function buildSummaryLines(): array
    {
        $lines = [];

        $lines[] = __('email.status') . ': <b>' . e($this->translateStatusKey('status.status', $this->order->status)) . '</b>';
        $lines[] = __('email.pay_status') . ': <b>' . e($this->translateStatusKey('status.pay_status', $this->order->pay_status)) . '</b>';
        $lines[] = __('email.delivery_status') . ': <b>' . e($this->translateStatusKey('status.delivery_status', $this->order->delivery_status)) . '</b>';
        $lines[] = __('email.currency') . ': <b>' . e($this->currency) . '</b>';

        $products = $this->order->productsAnyway ?? [];
        $lines[] = __('email.items_count') . ': <b>' . e((string) max(0, count($products))) . '</b>';

        return $this->filterLines($lines);
    }

    protected function buildPricingLines(): array
    {
        $lines = [];

        $lines[] = __('email.subtotal') . ': <b>' . e($this->formatMoney($this->order->subtotal)) . '</b>';
        $lines[] = __('email.shipping_total') . ': ' . e($this->formatMoney($this->order->shipping_total));
        $lines[] = __('email.tax_total') . ': ' . e($this->formatMoney($this->order->tax_total));

        if ((float) $this->order->promocode_discount_total > 0) {
            $lines[] = __('email.promocode_discount_total') . ': ' . e($this->formatMoney($this->order->promocode_discount_total, null, true));
        }

        if ((float) $this->order->personal_discount_total > 0) {
            $lines[] = __('email.personal_discount_total') . ': ' . e($this->formatMoney($this->order->personal_discount_total, null, true));
        }

        if ((float) $this->order->bonus_discount_total > 0) {
            $lines[] = __('email.bonus_discount_total') . ': ' . e($this->formatMoney($this->order->bonus_discount_total, null, true));
        }

        if ((float) $this->order->discount_total > 0) {
            $lines[] = __('email.discount_total') . ': ' . e($this->formatMoney($this->order->discount_total, null, true));
        }

        $lines[] = __('email.grand_total') . ': <b>' . e($this->formatMoney($this->order->grand_total)) . '</b>';

        return $this->filterLines($lines);
    }

    protected function buildAdjustmentLines(): array
    {
        $lines = [];

        $promocode = $this->order->promocode;

        if (is_array($promocode) && ! empty($promocode)) {
            $code = $promocode['code'] ?? ($promocode['title'] ?? null);

            if ($code) {
                $lines[] = __('email.promocode') . ': <b>' . e($code) . '</b>';
            }

            $valueLine = $this->formatPromocodeValue($promocode);

            if ($valueLine !== '') {
                $lines[] = __('email.promocode_value') . ': ' . e($valueLine);
            }
        }

        $personalDiscount = data_get($this->order->info, 'personalDiscount', []);

        if (is_array($personalDiscount) && (! empty($personalDiscount['amount']) || ! empty($personalDiscount['percent']))) {
            $parts = [];

            if (! empty($personalDiscount['percent'])) {
                $parts[] = number_format((float) $personalDiscount['percent'], 2) . '%';
            }

            if (! empty($personalDiscount['amount'])) {
                $parts[] = $this->formatMoney(
                    $personalDiscount['amount'],
                    $personalDiscount['currency'] ?? null,
                    true
                );
            }

            if ($parts) {
                $lines[] = __('email.personal_discount') . ': ' . e(implode(' / ', $parts));
            }
        }

        $bonusLines = $this->buildBonusLines();

        if ($bonusLines) {
            $lines = array_merge($lines, $bonusLines);
        }

        return $this->filterLines($lines);
    }

    protected function buildBonusLines(): array
    {
        $lines = [];
        $info = $this->order->info ?? [];
        $bonuses = data_get($info, 'bonuses', []);

        $points = (float) ($bonuses['points'] ?? 0);
        $fiatAmount = $bonuses['fiat_amount'] ?? ($info['bonusesUsed'] ?? null);
        $fiatCurrency = $bonuses['fiat_currency'] ?? $this->currency;

        if ($points > 0 || (float) $fiatAmount > 0) {
            $lines[] = __('email.bonuses') . ': <b>' . __('email.applied') . '</b>';

            if ($points > 0) {
                $lines[] = __('email.bonus_points') . ': <b>' . e(number_format($points, 2)) . '</b>';
            }

            if ($fiatAmount !== null && (float) $fiatAmount > 0) {
                $lines[] = __('email.bonus_amount') . ': <b>' . e($this->formatMoney($fiatAmount, $fiatCurrency, true)) . '</b>';
            }

            if (! empty($bonuses['wallet_currency'])) {
                $lines[] = __('email.bonus_wallet_currency') . ': ' . e($bonuses['wallet_currency']);
            }

            if (! empty($bonuses['requested_points']) && abs((float) $bonuses['requested_points'] - $points) > 0.01) {
                $lines[] = __('email.bonus_requested_points') . ': ' . e(number_format((float) $bonuses['requested_points'], 2));
            }

            if (! empty($bonuses['requested_fiat']) && abs((float) $bonuses['requested_fiat'] - (float) $fiatAmount) > 0.01) {
                $lines[] = __('email.bonus_requested_amount') . ': ' . e($this->formatMoney((float) $bonuses['requested_fiat'], $fiatCurrency, true));
            }

            if (! empty($bonuses['refunded'])) {
                $lines[] = __('email.bonus_refunded');
            }
        }

        return $this->filterLines($lines);
    }

    protected function buildCustomerLines(): array
    {
        $lines = [];
        $user = (array) data_get($this->order->info, 'user', []);

        $first = $user['firstname'] ?? ($user['first_name'] ?? null);
        $last = $user['lastname'] ?? ($user['last_name'] ?? null);
        $fullName = trim(implode(' ', array_filter([$first, $last])));

        if ($fullName !== '') {
            $lines[] = __('email.customer_name') . ': <b>' . e($fullName) . '</b>';
        }

        if (! empty($user['phone'])) {
            $lines[] = __('email.phone') . ': <b>' . e($user['phone']) . '</b>';
        }

        if (! empty($user['email'])) {
            $lines[] = __('email.email') . ': <b>' . e($user['email']) . '</b>';
        }

        $comment = data_get($this->order->info, 'comment') ?? ($user['comment'] ?? null);

        if ($comment) {
            $lines[] = __('email.comment') . ': ' . e($comment);
        }

        foreach ($user as $key => $value) {
            if (in_array($key, ['firstname', 'first_name', 'lastname', 'last_name', 'phone', 'email', 'comment'], true)) {
                continue;
            }

            $stringValue = $this->stringifyValue($value);

            if ($stringValue === '') {
                continue;
            }

            $lines[] = $this->resolveLabel((string) $key, 'user') . ': ' . e($stringValue);
        }

        return $this->filterLines($lines);
    }

    protected function buildDeliveryLines(): array
    {
        $lines = [];
        $delivery = (array) data_get($this->order->info, 'delivery', []);

        $lines[] = __('email.delivery_status') . ': <b>' . e($this->translateStatusKey('status.delivery_status', $this->order->delivery_status)) . '</b>';

        if (! empty($delivery['method'])) {
            $lines[] = __('email.method') . ': ' . e($this->translateDeliveryMethod((string) $delivery['method']));
        }

        $locationParts = [];

        foreach (['country', 'area', 'region', 'district', 'settlement', 'city'] as $field) {
            if (! empty($delivery[$field])) {
                $locationParts[] = e($delivery[$field]);
            }
        }

        if ($locationParts) {
            $lines[] = __('email.location') . ': ' . implode(', ', $locationParts);
        }

        $addressParts = [];

        if (! empty($delivery['street'])) {
            $addressParts[] = e($delivery['street']);
        }

        if (! empty($delivery['house'])) {
            $addressParts[] = __('email.house') . ' ' . e($delivery['house']);
        }

        if (! empty($delivery['room'])) {
            $addressParts[] = __('email.room') . ' ' . e($delivery['room']);
        }

        if ($addressParts) {
            $lines[] = __('email.address') . ': ' . implode(', ', $addressParts);
        }

        foreach (['warehouse', 'zip', 'tracking_number'] as $field) {
            if (! empty($delivery[$field])) {
                $lines[] = $this->resolveLabel($field, 'delivery') . ': ' . e($delivery[$field]);
            }
        }

        if (! empty($delivery['comment'])) {
            $lines[] = __('email.comment') . ': ' . e($delivery['comment']);
        }

        $quote = (array) data_get($this->order->info, 'shippingQuote', []);

        if ($quote) {
            $quoteCurrency = $quote['currency'] ?? $this->currency;

            if (isset($quote['amount'])) {
                $lines[] = __('email.shipping_quote') . ': ' . e($this->formatMoney($quote['amount'], $quoteCurrency));
            }

            if (! empty($quote['provider'])) {
                $lines[] = __('email.shipping_provider') . ': ' . e($quote['provider']);
            }

            if (! empty($quote['methodKey'])) {
                $lines[] = __('email.shipping_method') . ': ' . e($quote['methodKey']);
            }

            if (! empty($quote['breakdown']) && is_array($quote['breakdown'])) {
                foreach ($quote['breakdown'] as $label => $value) {
                    $stringValue = $this->stringifyValue($value);

                    if ($stringValue === '') {
                        continue;
                    }

                    if (is_numeric($value)) {
                        $stringValue = $this->formatMoney((float) $value, $quoteCurrency);
                    }

                    $lines[] = __('email.shipping_breakdown') . ' — ' . e($this->stringifyBreakdownLabel($label)) . ': ' . e($stringValue);
                }
            }
        }

        return $this->filterLines($lines);
    }

    protected function buildPaymentLines(): array
    {
        $lines = [];
        $payment = (array) data_get($this->order->info, 'payment', []);

        $lines[] = __('email.pay_status') . ': <b>' . e($this->translateStatusKey('status.pay_status', $this->order->pay_status)) . '</b>';

        if (! empty($payment['method'])) {
            $lines[] = __('email.method') . ': ' . e($this->translatePaymentMethod((string) $payment['method']));
        }

        foreach ($payment as $key => $value) {
            if ($key === 'method') {
                continue;
            }

            $stringValue = $this->stringifyValue($value);

            if ($stringValue === '') {
                continue;
            }

            if ($key === 'amount' && is_numeric($stringValue)) {
                $lines[] = __('email.amount') . ': ' . e($this->formatMoney((float) $stringValue));
                continue;
            }

            $lines[] = $this->resolveLabel((string) $key, 'payment') . ': ' . e($stringValue);
        }

        $invoiceDownload = $this->order->invoiceDownloadUrl;
        $invoiceQr = $this->order->invoiceQrUrl;

        $this->invoice = [
            'required' => $this->order->requiresInvoice(),
            'download_url' => $invoiceDownload,
            'qr_url' => $invoiceQr,
        ];

        if ($this->invoice['required'] && ! $invoiceDownload && ! $invoiceQr) {
            $lines[] = __('email.invoice_status_pending');
        }

        return $this->filterLines($lines);
    }

    protected function formatProducts(): array
    {
        $products = $this->order->productsAnyway ?? [];

        return array_map(function ($product) {
            $product = (array) $product;

            $price = (float) ($product['price'] ?? 0);
            $amount = (float) ($product['amount'] ?? 0);
            $total = $price * $amount;

            $product['price_formatted'] = $this->formatMoney($price);
            $product['total_formatted'] = $this->formatMoney($total);
            $product['currency'] = $this->currency;

            return $product;
        }, $products);
    }

    protected function formatMoney($value, ?string $currency = null, bool $signed = false): string
    {
        $amount = (float) ($value ?? 0);
        $currency = $currency ?: $this->currency;

        $prefix = '';

        if ($signed) {
            if ($amount > 0) {
                $prefix = '-';
            }

            $amount = abs($amount);
        }

        return $prefix . number_format($amount, 2, '.', ' ') . ' ' . $currency;
    }

    protected function translateStatusKey(string $baseKey, ?string $value): string
    {
        if (! $value) {
            return '';
        }

        $key = $baseKey . '.' . $value;
        $translation = __($key);

        if ($translation === $key) {
            return Str::title(str_replace('_', ' ', $value));
        }

        return $translation;
    }

    protected function translateDeliveryMethod(string $method): string
    {
        $key = 'validation.values.delivery.method.' . $method;
        $translation = __($key);

        if ($translation === $key) {
            return Str::title(str_replace(['_', '-'], ' ', $method));
        }

        return strip_tags($translation);
    }

    protected function translatePaymentMethod(string $method): string
    {
        $key = 'validation.values.payment.method.' . $method;
        $translation = __($key);

        if ($translation === $key) {
            $key = 'payment.method.' . $method;
            $translation = __($key);
        }

        if ($translation === $key) {
            return Str::title(str_replace(['_', '-'], ' ', $method));
        }

        return strip_tags($translation);
    }

    protected function resolveLabel(string $field, string $group): string
    {
        $map = [
            'user' => [
                'middle_name' => 'email.middlename',
                'patronymic' => 'email.middlename',
                'company' => 'email.company',
                'inn' => 'email.tax_number',
                'tax_number' => 'email.tax_number',
                'type' => 'email.customer_type',
            ],
            'delivery' => [
                'warehouse' => 'email.warehouse',
                'zip' => 'email.zip',
                'tracking_number' => 'email.tracking_number',
                'country' => 'email.country',
                'area' => 'email.area',
                'region' => 'email.region',
                'district' => 'email.district',
                'settlement' => 'email.settlement',
                'city' => 'email.city',
                'street' => 'email.street',
            ],
            'payment' => [
                'provider' => 'email.payment_provider',
                'account' => 'email.payment_account',
                'card' => 'email.payment_card',
                'reference' => 'email.payment_reference',
                'invoice_number' => 'email.invoice_number',
                'comment' => 'email.comment',
            ],
        ];

        $key = $map[$group][$field] ?? null;

        if ($key) {
            $translation = __($key);

            if ($translation !== $key) {
                return $translation;
            }
        }

        return Str::title(str_replace(['_', '-'], ' ', $field));
    }

    protected function stringifyValue($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? __('email.yes') : __('email.no');
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        if (is_array($value)) {
            $parts = [];

            foreach ($value as $item) {
                $string = $this->stringifyValue($item);

                if ($string !== '') {
                    $parts[] = $string;
                }
            }

            return implode(', ', $parts);
        }

        return '';
    }

    protected function stringifyBreakdownLabel($label): string
    {
        if (is_string($label)) {
            return Str::title(str_replace(['_', '-'], ' ', $label));
        }

        if (is_scalar($label)) {
            return (string) $label;
        }

        return '';
    }

    protected function formatPromocodeValue(array $promocode): string
    {
        $type = $promocode['type'] ?? null;
        $value = $promocode['value'] ?? null;

        if ($value === null) {
            return '';
        }

        if ($type === 'percent') {
            return number_format((float) $value, 2) . '%';
        }

        return $this->formatMoney((float) $value, $promocode['currency'] ?? null, true);
    }

    protected function filterLines(array $lines): array
    {
        return array_values(array_filter($lines, static function ($line) {
            return $line !== null && $line !== '';
        }));
    }
}
