<?php

namespace ParabellumKoval\Dumper\Support;

final class AutoDumpSchedulePresets
{
    public static function definitions(): array
    {
        return [
            'every_30_minutes' => [
                'label' => 'Каждые 30 минут',
                'cron' => '*/30 * * * *',
            ],
            'hourly' => [
                'label' => 'Каждый час',
                'cron' => '0 * * * *',
            ],
            'every_2_hours' => [
                'label' => 'Каждые 2 часа',
                'cron' => '0 */2 * * *',
            ],
            'every_3_hours' => [
                'label' => 'Каждые 3 часа',
                'cron' => '0 */3 * * *',
            ],
            'every_6_hours' => [
                'label' => 'Каждые 6 часов',
                'cron' => '0 */6 * * *',
            ],
            'every_12_hours' => [
                'label' => 'Каждые 12 часов',
                'cron' => '0 */12 * * *',
            ],
            'daily' => [
                'label' => 'Каждый день',
                'cron' => '0 2 * * *',
            ],
            'every_3_days' => [
                'label' => 'Каждые 3 дня',
                'cron' => '0 2 */3 * *',
            ],
            'weekly' => [
                'label' => 'Каждую неделю',
                'cron' => '0 2 * * 1',
            ],
            'every_2_weeks' => [
                'label' => 'Каждые 2 недели',
                'cron' => '0 2 * * 1',
            ],
            'monthly' => [
                'label' => 'Каждый месяц',
                'cron' => '0 4 1 * *',
            ],
            'every_3_months' => [
                'label' => 'Каждые 3 месяца',
                'cron' => '0 4 1 */3 *',
            ],
            'every_6_months' => [
                'label' => 'Каждые полгода',
                'cron' => '0 4 1 */6 *',
            ],
            'yearly' => [
                'label' => 'Каждый год',
                'cron' => '0 4 1 1 *',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::definitions() as $key => $definition) {
            $options[$key] = $definition['label'];
        }

        return $options;
    }

    public static function cronFor(?string $key): ?string
    {
        $key = self::normalizeKey($key);

        if ($key === null) {
            return null;
        }

        return self::definitions()[$key]['cron'] ?? null;
    }

    public static function labelFor(?string $key): ?string
    {
        $key = self::normalizeKey($key);

        if ($key === null) {
            return null;
        }

        return self::definitions()[$key]['label'] ?? null;
    }

    public static function keyForCron(?string $cron): ?string
    {
        $cron = trim((string) $cron);

        if ($cron === '') {
            return null;
        }

        foreach (self::definitions() as $key => $definition) {
            if ($definition['cron'] === $cron) {
                return $key;
            }
        }

        return null;
    }

    public static function isDue(?string $key, \DateTimeInterface $now): bool
    {
        $key = self::normalizeKey($key);

        if ($key === null) {
            return true;
        }

        return match ($key) {
            'every_2_weeks' => ((int) $now->format('W')) % 2 === 1,
            default => true,
        };
    }

    protected static function normalizeKey(?string $key): ?string
    {
        $key = trim((string) $key);

        if ($key === '') {
            return null;
        }

        return self::aliases()[$key] ?? $key;
    }

    /**
     * @return array<string, string>
     */
    protected static function aliases(): array
    {
        return [
            'daily_01_00' => 'daily',
            'daily_02_00' => 'daily',
            'daily_04_00' => 'daily',
            'weekly_monday_02_00' => 'weekly',
            'monthly_first_day_04_00' => 'monthly',
        ];
    }
}
