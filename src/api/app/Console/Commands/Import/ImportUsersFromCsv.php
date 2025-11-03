<?php

namespace App\Console\Commands\Import;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Backpack\Profile\app\Models\Profile;

class ImportUsersFromCsv extends Command
{
    protected $signature = 'profile:import-users 
                            {path : Путь к CSV файлу} 
                            {--delimiter=, : Разделитель столбцов} 
                            {--chunk=500 : Размер чанка для транзакций} 
                            {--dry-run : Только показать, что будет импортировано}';

    protected $description = 'Импорт пользователей и профилей из CSV в users и ak_profiles по правилам проекта';

    /** Столбцы, которые НЕ переносим совсем */
    protected array $dropColumns = [
        'rich_editing','syntax_highlighting','admin_color','use_ssl','show_admin_bar_front',
        'wp_user_level','dismissed_wp_pointers','show_welcome_panel','session_tokens',
        'last_update','is_guest_user',
    ];

    /** Ключевые системные поля, которые маппятся отдельно (не кладём в meta) */
    protected array $reserved = [
        'ID','id','email','user_email','user_login','user_nicename','display_name','name',
        'first_name','last_name','locale','billing_country','roles','billing_phone','phone',
        'avatar_url','timezone','birthdate',
    ];

    /** Префиксы, которые собираем в структурированные блоки в meta */
    protected array $structuredPrefixes = ['billing_','shipping_'];

    public function handle(): int
    {
        $path      = (string) $this->argument('path');
        $delimiter = (string) $this->option('delimiter');
        $chunk     = (int) $this->option('chunk');
        $dryRun    = (bool) $this->option('dry-run');

        if (! is_readable($path)) {
            $this->error("Файл не найден или недоступен для чтения: {$path}");
            return self::FAILURE;
        }

        $rows = $this->readCsvLazy($path, $delimiter);
        $total = 0; $created = 0; $updated = 0; $skipped = 0;

        $this->info('Старт импорта… '.($dryRun ? '[DRY-RUN]' : ''));

        /** Обрабатываем чанками с транзакциями */
        foreach ($rows->chunk($chunk) as $chunkRows) {
            DB::beginTransaction();
            try {
                foreach ($chunkRows as $row) {
                    $total++;

                    // Приводим ключи к snake_case для удобства
                    $row = collect($row)->keyBy(function ($v, $k) {
                        return Str::snake(trim((string)$k));
                    })->all();

                    // Минимальная валидация email
                    $email = $row['email'] ?? $row['user_email'] ?? null;
                    if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $skipped++;
                        $this->warn("[$total] Пропуск: некорректный email");
                        continue;
                    }

                    // Имя: из display_name/name/first+last
                    $firstName = $row['first_name'] ?? null;
                    $lastName  = $row['last_name']  ?? null;
                    $display   = $row['display_name'] ?? $row['name'] ?? null;
                    $fullName  = $display ?: trim(implode(' ', array_filter([$firstName, $lastName]))) ?: Str::before($email, '@');

                    // discount_percent из roles
                    $discount = $this->extractDiscountPercent($row['roles'] ?? '');

                    // country_code ← billing_country (ISO2)
                    $countryCode = strtoupper(substr((string)($row['billing_country'] ?? ''), 0, 2)) ?: null;

                    // locale ← первые 2 буквы
                    $locale = $row['locale'] ?? null;
                    $locale = $locale ? strtolower(substr($locale, 0, 2)) : null;

                    // phone: приоритет billing_phone → phone
                    $phone = $row['billing_phone'] ?? ($row['phone'] ?? null);

                    // timezone, birthdate, avatar_url (если есть колонки в csv)
                    $timezone  = $row['timezone']   ?? null;
                    $birthdate = $this->parseDate($row['birthdate'] ?? null); // Y-m-d|d.m.Y|...
                    $avatarUrl = $row['avatar_url'] ?? null;

                    // meta: всё «прочее» + структурные billing_*/shipping_*
                    $meta = $this->buildMeta($row);

                    if ($dryRun) {
                        $this->line("[$total] {$email} | {$fullName} | discount={$discount} | country={$countryCode} | locale={$locale}");
                        continue;
                    }

                    /** upsert пользователя по email */
                    /** @var User $user */
                    $user = User::query()->where('email', $email)->first();

                    if (! $user) {
                        $user = new User();
                        $user->email             = $email;
                        $user->name              = $fullName;
                        $user->password          = Hash::make(Str::random(32)); // заглушка
                        $user->email_verified_at = Carbon::now();
                        $user->save();
                        $created++;
                    } else {
                        // Обновим имя, если пришло более информативно
                        if ($fullName && $fullName !== $user->name) {
                            $user->name = $fullName;
                            $user->save();
                        }
                        $updated++;
                    }

                    /** профиль */
                    /** @var Profile $profile */
                    $profile = Profile::query()->where('user_id', $user->id)->first();

                    if (! $profile) {
                        $profile = new Profile();
                        $profile->user_id = $user->id;
                        $profile->referral_code = $this->makeUniqueReferralCode();
                    }

                    // Заполнение профильных полей
                    $profile->first_name = $firstName ?: ($profile->first_name ?? null);
                    $profile->last_name  = $lastName  ?: ($profile->last_name  ?? null);
                    $profile->full_name  = $fullName ?: ($profile->full_name ?? null);
                    $profile->phone      = $phone ?: ($profile->phone ?? null);
                    $profile->country_code = $countryCode ?: ($profile->country_code ?? null);
                    if ($locale)   $profile->locale   = $locale;
                    if ($timezone) $profile->timezone = $timezone;
                    if ($birthdate) $profile->birthdate = $birthdate;
                    if ($avatarUrl) $profile->avatar_url = $avatarUrl;

                    // персональная скидка
                    if (! is_null($discount)) {
                        $profile->discount_percent = $discount;
                    } elseif (is_null($profile->discount_percent)) {
                        $profile->discount_percent = 0;
                    }

                    // слияние meta
                    $existingMeta = $profile->meta ?? [];
                    if (is_string($existingMeta)) {
                        $existingMeta = json_decode($existingMeta, true) ?: [];
                    }
                    $profile->meta = $this->deepMerge($existingMeta, $meta);

                    $profile->save();
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error('Ошибка чанка: '.$e->getMessage());
                return self::FAILURE;
            }
        }

        if ($dryRun) {
            $this->info("Проверка завершена: строк всего={$total} (ничего не записано).");
        } else {
            $this->info("Импорт завершён: всего={$total}, создано={$created}, обновлено={$updated}, пропущено={$skipped}.");
        }

        return self::SUCCESS;
    }

    /** Читаем CSV лениво с первой строки-заголовка */
    protected function readCsvLazy(string $path, string $delimiter = ','): LazyCollection
    {
        $fh = fopen($path, 'rb');
        if (! $fh) {
            throw new \RuntimeException("Не удалось открыть файл: {$path}");
        }

        return LazyCollection::make(function () use ($fh, $delimiter) {
            $header = null;
            while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {
                // пропускаем пустые строки
                if ($row === [null] || $row === false) { continue; }

                if ($header === null) {
                    $header = array_map(fn($h) => trim((string)$h), $row);
                    continue;
                }
                $assoc = [];
                foreach ($header as $i => $key) {
                    $assoc[$key] = $row[$i] ?? null;
                }
                yield $assoc;
            }
            fclose($fh);
        });
    }

    /** discount_5 → 5.00; discount_10 → 10.00; иначе null */
    protected function extractDiscountPercent(?string $roles): ?float
    {
        if (! $roles) return null;
        $roles = strtolower($roles);

        // допускаем форматы: "discount_5", "customer,discount_10", "['discount_5','subscriber']" и т.п.
        if (str_contains($roles, 'discount_10')) return 10.00;
        if (str_contains($roles, 'discount_5'))  return 5.00;

        return null;
    }

    /** d.m.Y, Y-m-d, m/d/Y → Y-m-d или null */
    protected function parseDate(?string $value): ?string
    {
        if (! $value) return null;
        $value = trim($value);

        $formats = ['Y-m-d','d.m.Y','d/m/Y','m/d/Y','Y/m/d'];
        foreach ($formats as $fmt) {
            $dt = Carbon::createFromFormat($fmt, $value);
            if ($dt && $dt->isValid()) {
                return $dt->format('Y-m-d');
            }
        }
        // крайний случай: попытаться как свободный парсинг
        try {
            $dt = Carbon::parse($value);
            return $dt->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /** Собираем meta: structured billing_* shipping_* + все неиспользованные столбцы (кроме dropColumns/reserved) */
    protected function buildMeta(array $row): array
    {
        $meta = [];

        // 1) structured billing_*/shipping_*
        foreach ($this->structuredPrefixes as $prefix) {
            $group = rtrim($prefix, '_'); // billing / shipping
            $bucket = [];

            foreach ($row as $key => $value) {
                if (str_starts_with($key, $prefix)) {
                    $subKey = substr($key, strlen($prefix)); // first_name, last_name...
                    $bucket[$subKey] = $value;
                }
            }
            if (! empty($bucket)) {
                // Нормализуем известные поля имени/телефона, чтобы не потерять связь:
                // (ничего не перетираем в БД — это только meta)
                $meta[$group] = $bucket;
            }
        }

        // 2) все прочие «неизвестные» поля → meta.other
        $other = [];
        foreach ($row as $key => $value) {
            if (in_array($key, $this->dropColumns, true)) {
                continue;
            }
            // то, что маппится на отдельные столбцы профиля/юзера — пропускаем
            if (in_array($key, $this->reserved, true)) {
                continue;
            }
            // уже включено в structured
            foreach ($this->structuredPrefixes as $prefix) {
                if (str_starts_with($key, $prefix)) {
                    continue 2;
                }
            }
            // технические или пустые — опционально фильтруем
            if ($value === '' || $value === null) {
                continue;
            }
            $other[$key] = $value;
        }
        if (! empty($other)) {
            $meta['other'] = $other;
        }

        return $meta;
    }

    /** Глубокое слияние массивов */
    protected function deepMerge(array $base, array $append): array
    {
        foreach ($append as $k => $v) {
            if (is_array($v) && isset($base[$k]) && is_array($base[$k])) {
                $base[$k] = $this->deepMerge($base[$k], $v);
            } else {
                $base[$k] = $v;
            }
        }
        return $base;
    }

    /** Генерация уникального referral_code (32 симв. макс) */
    protected function makeUniqueReferralCode(): string
    {
        do {
            // 12 символов base36, верхний регистр — удобно читать/вводить
            $code = strtoupper(Str::random(8)).Str::upper(Str::padLeft(base_convert((string)random_int(0, PHP_INT_MAX), 10, 36), 4, '0'));
            $exists = Profile::query()->where('referral_code', $code)->exists();
        } while ($exists);

        return substr($code, 0, 32);
    }
}
