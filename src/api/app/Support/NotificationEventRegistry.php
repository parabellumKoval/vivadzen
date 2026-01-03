<?php

namespace App\Support;

use Backpack\Profile\app\Models\NotificationEvent;

class NotificationEventRegistry
{
    public function ensure(string $key): NotificationEvent
    {
        $event = NotificationEvent::query()->firstOrCreate(
            ['key' => $key],
            $this->defaults($key)
        );

        $this->syncDefaults($event, $key);

        return $event;
    }

    protected function defaults(string $key): array
    {
        $definitions = $this->definitions();

        return $definitions[$key] ?? [];
    }

    protected function syncDefaults(NotificationEvent $event, string $key): void
    {
        $defaults = $this->defaults($key);

        if ($defaults === []) {
            return;
        }

        $dirty = false;

        foreach (['name', 'title', 'excerpt', 'body'] as $field) {
            if (! array_key_exists($field, $defaults) || ! is_array($defaults[$field])) {
                continue;
            }

            $current = $event->getTranslations($field) ?? [];
            $merged = $this->mergeTranslations($current, $defaults[$field]);

            if ($merged !== $current) {
                $event->setTranslations($field, $merged);
                $dirty = true;
            }
        }

        if (array_key_exists('meta', $defaults) && is_array($defaults['meta'])) {
            $meta = is_array($event->meta ?? null) ? $event->meta : [];
            $updatedMeta = $this->mergeMetaDefaults($meta, $defaults['meta']);

            if ($updatedMeta !== $meta) {
                $event->meta = $updatedMeta;
                $dirty = true;
            }
        }

        if ($dirty) {
            $event->save();
        }
    }

    protected function definitions(): array
    {
        return [
            'referral.attached' => $this->eventDefinition('notifications.events.referral.attached', [
                'variant' => NotificationEvent::VARIANT_SUCCESS,
                'audience' => NotificationEvent::AUDIENCE_AUTHENTICATED,
                'target_type' => NotificationEvent::TARGET_PERSONAL,
                'icon' => '✨',
                'meta' => $this->actionMeta('/account/network/referrals', 'notifications.actions.view_referrals'),
            ]),
            'wallet.reward.created' => $this->eventDefinition('notifications.events.wallet.reward.created', [
                'variant' => NotificationEvent::VARIANT_SUCCESS,
                'audience' => NotificationEvent::AUDIENCE_AUTHENTICATED,
                'target_type' => NotificationEvent::TARGET_PERSONAL,
                'icon' => '💰',
                'meta' => $this->actionMeta('/account/transactions', 'notifications.actions.open_transactions'),
            ]),
            'withdrawal.approved' => $this->eventDefinition('notifications.events.withdrawal.approved', [
                'variant' => NotificationEvent::VARIANT_INFO,
                'audience' => NotificationEvent::AUDIENCE_AUTHENTICATED,
                'target_type' => NotificationEvent::TARGET_PERSONAL,
                'icon' => '📤',
                'meta' => $this->actionMeta('/account/transactions', 'notifications.actions.open_transactions'),
            ]),
            'withdrawal.paid' => $this->eventDefinition('notifications.events.withdrawal.paid', [
                'variant' => NotificationEvent::VARIANT_SUCCESS,
                'audience' => NotificationEvent::AUDIENCE_AUTHENTICATED,
                'target_type' => NotificationEvent::TARGET_PERSONAL,
                'icon' => '💸',
                'meta' => $this->actionMeta('/account/transactions', 'notifications.actions.open_transactions'),
            ]),
            'review.published' => $this->eventDefinition('notifications.events.review.published', [
                'variant' => NotificationEvent::VARIANT_SUCCESS,
                'audience' => NotificationEvent::AUDIENCE_AUTHENTICATED,
                'target_type' => NotificationEvent::TARGET_PERSONAL,
                'icon' => '📝',
            ]),
            'order.status.changed' => $this->eventDefinition('notifications.events.order.status.changed', [
                'variant' => NotificationEvent::VARIANT_INFO,
                'audience' => NotificationEvent::AUDIENCE_AUTHENTICATED,
                'target_type' => NotificationEvent::TARGET_PERSONAL,
                'icon' => '📦',
                'meta' => $this->actionMeta('/account/orders', 'notifications.actions.open_orders'),
            ]),
            'order.payment.changed' => $this->eventDefinition('notifications.events.order.payment.changed', [
                'variant' => NotificationEvent::VARIANT_INFO,
                'audience' => NotificationEvent::AUDIENCE_AUTHENTICATED,
                'target_type' => NotificationEvent::TARGET_PERSONAL,
                'icon' => '💳',
                'meta' => $this->actionMeta('/account/orders', 'notifications.actions.open_orders'),
            ]),
            'order.delivery.changed' => $this->eventDefinition('notifications.events.order.delivery.changed', [
                'variant' => NotificationEvent::VARIANT_INFO,
                'audience' => NotificationEvent::AUDIENCE_AUTHENTICATED,
                'target_type' => NotificationEvent::TARGET_PERSONAL,
                'icon' => '🚚',
                'meta' => $this->actionMeta('/account/orders', 'notifications.actions.open_orders'),
            ]),
        ];
    }

    protected function eventDefinition(string $baseKey, array $overrides = []): array
    {
        return array_merge([
            'name' => $this->translations($baseKey . '.name'),
            'title' => $this->translations($baseKey . '.title'),
            'excerpt' => $this->translations($baseKey . '.excerpt'),
            'body' => $this->translations($baseKey . '.body'),
        ], $overrides);
    }

    protected function actionMeta(string $url, string $labelKey): array
    {
        return [
            'action_url' => $url,
            'action_label' => $this->translations($labelKey),
        ];
    }

    protected function translations(string $key): array
    {
        $translations = [];

        foreach ($this->locales() as $locale) {
            $translations[$locale] = __($key, [], $locale);
        }

        return $translations;
    }

    protected function locales(): array
    {
        $locales = (array) config('app.supported_locales', []);

        return array_values(array_filter($locales, fn ($locale) => is_string($locale) && $locale !== ''));
    }

    protected function mergeTranslations(array $current, array $defaults): array
    {
        $merged = $current;

        foreach ($defaults as $locale => $value) {
            if (! array_key_exists($locale, $merged) || ! filled($merged[$locale])) {
                $merged[$locale] = $value;
            }
        }

        return $merged;
    }

    protected function mergeMetaDefaults(array $current, array $defaults): array
    {
        $merged = $current;

        if (array_key_exists('action_url', $defaults) && empty($merged['action_url'])) {
            $merged['action_url'] = $defaults['action_url'];
        }

        if (array_key_exists('action_label', $defaults) && is_array($defaults['action_label'])) {
            $actionLabel = $merged['action_label'] ?? null;

            if (is_array($actionLabel)) {
                $mergedLabel = $this->mergeTranslations($actionLabel, $defaults['action_label']);
                $merged['action_label'] = $mergedLabel;
            } elseif (! filled($actionLabel)) {
                $merged['action_label'] = $defaults['action_label'];
            }
        }

        return $merged;
    }
}
