<?php

namespace App\Mail;

use App\Mail\Concerns\InteractsWithRegionalContext;
use Backpack\Feedback\app\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Buy1ClickCreatedAdmin extends Mailable
{
    use Queueable, SerializesModels;
    use InteractsWithRegionalContext;

    public Feedback $feedback;
    public array $contactLines = [];
    public array $requestLines = [];
    public array $productLines = [];

    public function __construct(Feedback $feedback)
    {
        $this->feedback = $feedback;
        $this->initializeRegionalContext([
            'locale' => 'ru',
            'accept_language' => 'ru',
        ]);
        $this->setRegionalRegion(null);
    }

    public function build()
    {
        $this->contactLines = $this->buildContactLines();
        $this->requestLines = $this->buildRequestLines();
        $this->productLines = $this->buildProductLines();

        $type = strtolower(trim((string) $this->feedback->type));
        $phone = (string) ($this->feedback->phone ?? '');
        $isSampleSet = $type === 'landing_sample_set';

        $subject = $isSampleSet
            ? __('mail.feedback.sample_set_subject', ['phone' => $phone])
            : __('mail.feedback.one_click_subject', ['phone' => $phone]);

        $title = $isSampleSet
            ? __('mail.feedback.sample_set_title')
            : __('mail.feedback.title');

        return $this->subject($subject)
            ->markdown('mail.buy1click_created_admin')
            ->with($this->regionalViewData([
                'feedback' => $this->feedback,
                'contactLines' => $this->contactLines,
                'requestLines' => $this->requestLines,
                'productLines' => $this->productLines,
                'title' => $title,
            ]));
    }

    protected function buildContactLines(): array
    {
        $labels = __('mail.feedback.labels');

        return array_values(array_filter([
            $this->simpleLine($labels['name'] ?? 'Имя', $this->feedback->name),
            $this->simpleLine($labels['phone'] ?? 'Телефон', $this->feedback->phone),
            $this->simpleLine($labels['email'] ?? 'Email', $this->feedback->email),
        ]));
    }

    protected function buildRequestLines(): array
    {
        $labels = __('mail.feedback.labels');

        return array_values(array_filter([
            $this->simpleLine($labels['type'] ?? 'Тип', $this->feedback->type),
            $this->richLine($labels['comment'] ?? 'Комментарий', $this->feedback->text),
            $this->simpleLine(
                $labels['created_at'] ?? 'Создано',
                optional($this->feedback->created_at)->format('d.m.Y H:i')
            ),
        ]));
    }

    protected function buildProductLines(): array
    {
        $product = $this->productArray();
        if (empty($product)) {
            return [];
        }

        $labels = __('mail.feedback.labels');

        return array_values(array_filter([
            $this->simpleLine($labels['product'] ?? 'Товар', $product['name'] ?? null),
            $this->simpleLine($labels['product_code'] ?? 'Артикул', $product['code'] ?? null),
            $this->simpleLine($labels['product_id'] ?? 'ID товара', isset($product['id']) ? (string) $product['id'] : null),
            $this->simpleLine(
                $labels['price'] ?? 'Цена',
                isset($product['price']) ? $this->formatMoney($product['price']) : null
            ),
            $this->simpleLine(
                $labels['old_price'] ?? 'Старая цена',
                isset($product['oldPrice']) ? $this->formatMoney($product['oldPrice']) : null
            ),
            $this->simpleLine(
                $labels['in_stock'] ?? 'Остаток',
                isset($product['inStock']) ? (string) $product['inStock'] : null
            ),
            $this->simpleLine($labels['slug'] ?? 'Слаг', $product['slug'] ?? null),
        ]));
    }

    protected function simpleLine(string $label, $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        return e($label) . ': <b>' . e((string) $value) . '</b>';
    }

    protected function richLine(string $label, ?string $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return null;
        }

        return e($label) . ': <b>' . nl2br(e($value)) . '</b>';
    }

    protected function formatMoney($value): string
    {
        return number_format((float) $value, 2, '.', ' ');
    }

    protected function productArray(): array
    {
        $extras = $this->feedback->extras ?? [];

        if (is_array($extras)) {
            return $extras;
        }

        if (is_string($extras) && $extras !== '') {
            $decoded = json_decode($extras, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}
