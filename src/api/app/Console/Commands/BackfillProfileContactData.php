<?php

namespace App\Console\Commands;

use Backpack\Profile\app\Models\Profile;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class BackfillProfileContactData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profile:backfill-contact-data 
                            {--chunk=200 : Количество профилей за итерацию}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Заполняет отсутствующие first_name, last_name, full_name и phone из meta.billing/meta.shipping';

    public function handle(): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $processed = 0;
        $updated = 0;

        $total = Profile::query()->count();

        if ($total === 0) {
            $this->comment('Нет профилей для обработки.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Обработка %d профилей (шаг %d)...', $total, $chunkSize));

        Profile::query()
            ->orderBy('id')
            ->chunkById($chunkSize, function ($profiles) use (&$processed, &$updated) {
                foreach ($profiles as $profile) {
                    $processed++;
                    $changes = $this->buildUpdates($profile);

                    if ($changes === []) {
                        continue;
                    }

                    $profile->forceFill($changes);
                    $profile->saveQuietly();
                    $updated++;

                    if ($this->output->isVerbose()) {
                        $this->line(sprintf(
                            'Профиль #%d: обновлены поля %s',
                            $profile->getKey(),
                            implode(', ', array_keys($changes))
                        ));
                    }
                }
            });

        $this->info(sprintf('Завершено. Обработано %d профилей, обновлено %d.', $processed, $updated));

        return self::SUCCESS;
    }

    protected function buildUpdates(Profile $profile): array
    {
        $billing = $profile->getMetaSection('billing');
        $shipping = $profile->getMetaSection('shipping');
        $sources = [$billing, $shipping];

        $updates = [];

        if (!$this->hasValue($profile->first_name)) {
            $firstName = $this->valueFromAddresses($sources, 'first_name');
            if ($firstName !== null) {
                $updates['first_name'] = $firstName;
            }
        }

        if (!$this->hasValue($profile->last_name)) {
            $lastName = $this->valueFromAddresses($sources, 'last_name');
            if ($lastName !== null) {
                $updates['last_name'] = $lastName;
            }
        }

        if (!$this->hasValue($profile->phone)) {
            $phone = $this->valueFromAddresses($sources, 'phone');
            if ($phone !== null) {
                $updates['phone'] = $phone;
            }
        }

        if (!$this->hasValue($profile->full_name)) {
            $fullName = $this->makeFullName(
                $updates['first_name'] ?? $profile->first_name,
                $updates['last_name'] ?? $profile->last_name
            );

            if ($fullName !== null) {
                $updates['full_name'] = $fullName;
            }
        }

        return $updates;
    }

    protected function valueFromAddresses(array $addresses, string $key): ?string
    {
        foreach ($addresses as $address) {
            $value = Arr::get(is_array($address) ? $address : [], $key);

            if (!is_string($value)) {
                continue;
            }

            $value = trim($value);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function hasValue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return true;
    }

    protected function makeFullName(?string $firstName, ?string $lastName): ?string
    {
        $parts = [];

        foreach ([$firstName, $lastName] as $part) {
            if (!$this->hasValue($part)) {
                continue;
            }

            $parts[] = trim((string) $part);
        }

        if ($parts === []) {
            return null;
        }

        return implode(' ', $parts);
    }
}
