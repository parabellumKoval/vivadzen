<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class FixCategoryExtrasTransLocale extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'categories:fix-extras-locale';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Переименовать единственный перевод extras_trans у категорий с ru на en';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Поиск категорий с extras_trans только для ru...');

        // extras_trans хранится как spatie translatable + fake column
        // Забираем категории, у которых поле не null
        $query = Category::query()->whereNotNull('extras_trans');

        $bar = $this->output->createProgressBar($query->count());
        $bar->start();

        $updated = 0;

        $query->chunkById(200, function ($categories) use (&$updated, $bar) {
            foreach ($categories as $category) {
                /** @var Category $category */
                $translations = $category->getTranslations('extras_trans');

                // Нам подходит случай, когда есть только ключ ru
                if (count($translations) !== 1 || !array_key_exists('ru', $translations)) {
                    $bar->advance();
                    continue;
                }

                $value = $translations['ru'];

                // Не трогаем содержимое, только ключ
                unset($translations['ru']);
                $translations['en'] = $value;

                $category->setTranslations('extras_trans', $translations);
                $category->save();

                $updated++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Готово. Обновлено категорий: {$updated}");

        return self::SUCCESS;
    }
}
