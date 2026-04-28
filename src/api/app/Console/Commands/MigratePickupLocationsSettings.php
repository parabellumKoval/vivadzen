<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ParabellumKoval\Webhooks\Services\WebhookDispatcher;

class MigratePickupLocationsSettings extends Command
{
    protected $signature = 'settings:migrate-pickup-locations
                            {--force : Перезаписать уже заполненные site.contacts.pickup_locations}
                            {--dry-run : Показать изменения без записи в БД}';

    protected $description = 'Переносит legacy-значения address/schedule/map в первый элемент массива site.contacts.pickup_locations.';

    public function handle(): int
    {
        $table = config('backpack-settings.table', 'ak_settings');
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');
        $tableColumns = Schema::getColumnListing($table);

        $legacyRows = DB::table($table)
            ->whereIn('key', ['site.contacts.address', 'site.contacts.schedule', 'site.contacts.map'])
            ->orderBy('region')
            ->orderBy('locale')
            ->get();

        $sourceRows = $legacyRows
            ->where('key', 'site.contacts.address')
            ->values();
        $rowsByKey = $legacyRows->groupBy('key');

        if ($sourceRows->isEmpty()) {
            $this->comment('Не найдено legacy-настроек site.contacts.address.');

            return self::SUCCESS;
        }

        $processed = 0;
        $migrated = 0;
        $skipped = 0;
        $now = now();

        foreach ($sourceRows as $row) {
            $processed++;

            $address = $this->extractLegacyText($row->value, ['address', 'value', 'label', 'title', 'name']);
            if ($address === null) {
                $skipped++;
                continue;
            }

            $schedule = $this->resolveLegacyContextValue(
                $rowsByKey->get('site.contacts.schedule', collect()),
                $row->region,
                $row->locale,
                ['schedule', 'value', 'label', 'title', 'name']
            );
            $map = $this->resolveLegacyContextValue(
                $rowsByKey->get('site.contacts.map', collect()),
                $row->region,
                $row->locale,
                ['map', 'src', 'value']
            );

            $targetQuery = DB::table($table)->where('key', 'site.contacts.pickup_locations');
            $this->applyContext($targetQuery, $row->region, $row->locale);
            $existing = $targetQuery->first();
            $existingLocations = $this->decodeLocations($existing->value ?? null);
            $updatedLocations = $this->mergeLegacyIntoLocations($existingLocations, $address, $schedule, $map, $force);

            $payloadValue = json_encode($updatedLocations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($payloadValue === false) {
                $this->error(sprintf(
                    'Не удалось закодировать значение для [%s | %s].',
                    $row->region ?? 'global',
                    $row->locale ?? 'default'
                ));

                return self::FAILURE;
            }

            $existingValue = json_encode($existingLocations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($existingValue === $payloadValue) {
                $skipped++;

                if ($this->output->isVerbose()) {
                    $this->line(sprintf(
                        'Пропуск [%s | %s]: дополнительных данных для переноса нет.',
                        $row->region ?? 'global',
                        $row->locale ?? 'default'
                    ));
                }

                continue;
            }

            if ($dryRun) {
                $migrated++;
                $this->line(sprintf(
                    '[dry-run] [%s | %s] %s',
                    $row->region ?? 'global',
                    $row->locale ?? 'default',
                    implode(' | ', array_filter([$address, $schedule, $map]))
                ));
                continue;
            }

            $payload = [
                'key' => 'site.contacts.pickup_locations',
                'value' => $payloadValue,
                'cast' => 'array',
                'region' => $row->region,
                'locale' => $row->locale,
            ];

            if (in_array('group', $tableColumns, true)) {
                $payload['group'] = $existing->group ?? $row->group ?? 'site';
            }

            if (in_array('updated_at', $tableColumns, true)) {
                $payload['updated_at'] = $now;
            }

            if ($existing) {
                DB::table($table)
                    ->where('id', $existing->id)
                    ->update($payload);
            } else {
                if (in_array('created_at', $tableColumns, true)) {
                    $payload['created_at'] = $now;
                }

                DB::table($table)->insert($payload);
            }

            $migrated++;

            if ($this->output->isVerbose()) {
                $this->line(sprintf(
                    'Перенесено [%s | %s]: %s',
                    $row->region ?? 'global',
                    $row->locale ?? 'default',
                    $address
                ));
            }
        }

        if (!$dryRun && $migrated > 0) {
            $manager = app('backpack.settings');
            $manager->invalidate('site.contacts.address');
            $manager->invalidate('site.contacts.schedule');
            $manager->invalidate('site.contacts.map');
            $manager->invalidate('site.contacts.pickup_locations');
            app(WebhookDispatcher::class)->dispatch('refresh_settings', 'settings');
        }

        $this->info(sprintf(
            'Готово. Обработано %d, перенесено %d, пропущено %d.',
            $processed,
            $migrated,
            $skipped
        ));

        if ($dryRun) {
            $this->comment('Dry-run режим: изменения в БД не записывались.');
        }

        return self::SUCCESS;
    }

    protected function applyContext($query, ?string $region, ?string $locale): void
    {
        $region === null
            ? $query->whereNull('region')
            : $query->where('region', $region);

        $locale === null
            ? $query->whereNull('locale')
            : $query->where('locale', $locale);
    }

    protected function extractLegacyText(mixed $value, array $preferredKeys = []): ?string
    {
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return null;
            }

            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->extractLegacyText($decoded, $preferredKeys);
            }

            return $trimmed;
        }

        if (!is_array($value)) {
            return null;
        }

        $keys = $preferredKeys !== []
            ? $preferredKeys
            : ['address', 'schedule', 'map', 'src', 'value', 'label', 'title', 'name'];

        foreach ($keys as $key) {
            $candidate = $value[$key] ?? null;
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        if ($preferredKeys !== [] && !array_is_list($value)) {
            return null;
        }

        foreach ($value as $item) {
            if (is_string($item) && trim($item) !== '') {
                return trim($item);
            }

            if (is_array($item)) {
                $candidate = $this->extractLegacyText($item, $preferredKeys);
                if ($candidate !== null) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    protected function resolveLegacyContextValue(Collection $rows, ?string $region, ?string $locale, array $preferredKeys): ?string
    {
        if ($rows->isEmpty()) {
            return null;
        }

        $variants = [
            [$region, $locale],
            [$region, null],
            [null, $locale],
            [null, null],
        ];

        foreach ($variants as [$candidateRegion, $candidateLocale]) {
            $match = $rows->first(function ($row) use ($candidateRegion, $candidateLocale) {
                return $row->region === $candidateRegion && $row->locale === $candidateLocale;
            });

            if ($match) {
                return $this->extractLegacyText($match->value, $preferredKeys);
            }
        }

        return null;
    }

    /**
     * @param array<int, mixed> $locations
     * @return array<int, mixed>
     */
    protected function mergeLegacyIntoLocations(array $locations, string $address, ?string $schedule, ?string $map, bool $force): array
    {
        $updated = array_values($locations);
        $first = $this->normalizeLocation($updated[0] ?? null);

        foreach ([
            'address' => $address,
            'schedule' => $schedule,
            'map' => $map,
        ] as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $current = trim((string) ($first[$key] ?? ''));
            if ($force || $current === '') {
                $first[$key] = $value;
            }
        }

        if ($updated === []) {
            return [$first];
        }

        $updated[0] = $first;

        return $updated;
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeLocation(mixed $value): array
    {
        if (is_string($value)) {
            return [
                'title' => '',
                'address' => trim($value),
                'schedule' => '',
                'map' => '',
            ];
        }

        if (!is_array($value)) {
            return [
                'title' => '',
                'address' => '',
                'schedule' => '',
                'map' => '',
            ];
        }

        return array_merge($value, [
            'title' => $this->extractLegacyText($value, ['title', 'name', 'label']) ?? '',
            'address' => $this->extractLegacyText($value, ['address', 'value']) ?? '',
            'schedule' => $this->extractLegacyText($value, ['schedule']) ?? '',
            'map' => $this->extractLegacyText($value, ['map', 'src']) ?? '',
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function decodeLocations(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [];
            }

            $value = $decoded;
        }

        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, function ($item) {
            if (is_string($item)) {
                return trim($item) !== '';
            }

            if (!is_array($item)) {
                return false;
            }

            return trim((string) ($item['address'] ?? $item['value'] ?? '')) !== '';
        }));
    }
}
