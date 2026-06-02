# PHASE 01 — База данных и модели

> Зависит от: ничего. Это первая фаза.
> Время реализации: 30–60 минут.

## Что нужно сделать

1. Миграция `2026_06_05_000010_create_wiki_tables.php`.
2. Модели `WikiCategory`, `WikiArticle`, `WikiArticleRelation` (pivot можно без модели).
3. Сидер `WikiCategoriesSeeder` (4 категории, без статей).
4. Подключить сидер в `DatabaseSeeder` (если есть условие «только в dev» —
   соблюсти).
5. Прогнать миграцию и сидер. Убедиться, что таблицы созданы и в
   `wiki_categories` 4 строки.

## Команды для запуска

```bash
cd src/new-kratom/app
php artisan make:migration create_wiki_tables --create=wiki_articles
# (заменить содержимое на код ниже)
php artisan migrate
php artisan db:seed --class=WikiCategoriesSeeder
```

---

## 1. Миграция

**Файл:** `src/new-kratom/app/database/migrations/2026_06_05_000010_create_wiki_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Průvodce — wiki-энциклопедия о кратоме.
 *
 * Три таблицы:
 *  - wiki_categories — 4 категории (botanika, historie, legislativa, kvalita)
 *  - wiki_articles   — статьи с body (HTML от TipTap), seo-полями,
 *                      meta-override, cover (опционально).
 *  - wiki_article_related — ручная перелинковка «Související články»
 *                           (n:n self-referencing).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wiki_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('title', 160);
            $table->string('eyebrow', 120)->nullable(); // надзаголовок над H1
            $table->text('description')->nullable();    // 2–3 предложения для landing
            $table->string('icon', 32)->nullable();     // lucide name или emoji
            $table->string('accent', 16)->default('grass'); // для SCSS-модификаторов hero
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'position']);
        });

        Schema::create('wiki_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wiki_category_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 180)->unique();
            $table->string('title', 200);
            $table->string('excerpt', 320)->nullable();   // карточка каталога
            $table->longText('body');                     // HTML от TipTap

            $table->string('cover_path')->nullable();     // public-disk path
            $table->string('cover_url')->nullable();      // если внешний URL
            $table->string('cover_alt')->nullable();

            // SEO — публикуются в админке отдельной секцией «🎯 SEO»
            $table->string('seo_keyword', 160)->nullable();        // primary keyword
            $table->json('seo_secondary_keywords')->nullable();    // string[]
            $table->string('seo_search_intent', 32)->default('informational');
            $table->unsignedInteger('seo_volume_estimate')->nullable(); // monthly searches, info-only
            $table->string('seo_meta_title', 200)->nullable();
            $table->string('seo_meta_description', 320)->nullable();

            $table->unsignedSmallInteger('reading_time_minutes')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->string('status', 24)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedInteger('views_count')->default(0);

            $table->timestamps();

            $table->index(['wiki_category_id', 'status', 'position']);
            $table->index(['wiki_category_id', 'published_at']);
        });

        Schema::create('wiki_article_related', function (Blueprint $table) {
            $table->foreignId('wiki_article_id')->constrained('wiki_articles')->cascadeOnDelete();
            $table->foreignId('related_id')->constrained('wiki_articles')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->primary(['wiki_article_id', 'related_id']);
            $table->index('related_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wiki_article_related');
        Schema::dropIfExists('wiki_articles');
        Schema::dropIfExists('wiki_categories');
    }
};
```

---

## 2. Модели

### `src/new-kratom/app/app/Models/WikiCategory.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WikiCategory extends Model
{
    protected $fillable = [
        'slug', 'title', 'eyebrow', 'description',
        'icon', 'accent', 'position', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(WikiArticle::class)->orderBy('position');
    }

    public function publishedArticles(): HasMany
    {
        return $this->articles()->published();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('position');
    }
}
```

### `src/new-kratom/app/app/Models/WikiArticle.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class WikiArticle extends Model
{
    protected $fillable = [
        'wiki_category_id',
        'slug', 'title', 'excerpt', 'body',
        'cover_path', 'cover_url', 'cover_alt',
        'seo_keyword', 'seo_secondary_keywords', 'seo_search_intent',
        'seo_volume_estimate', 'seo_meta_title', 'seo_meta_description',
        'reading_time_minutes', 'position', 'status', 'published_at',
    ];

    protected $casts = [
        'seo_secondary_keywords' => 'array',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(WikiCategory::class, 'wiki_category_id');
    }

    /**
     * Ручная перелинковка «Související články» — статьи, которые автор
     * курирует на форме редактирования.
     */
    public function related(): BelongsToMany
    {
        return $this->belongsToMany(
            WikiArticle::class,
            'wiki_article_related',
            'wiki_article_id',
            'related_id',
        )->withPivot('position')->orderBy('wiki_article_related.position');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function coverDisplayUrl(): ?string
    {
        if ($this->cover_path) {
            return Storage::disk('public')->url($this->cover_path);
        }
        return $this->cover_url;
    }

    public function metaTitle(): string
    {
        return $this->seo_meta_title ?: $this->title . ' | Vivadzen Průvodce';
    }

    public function metaDescription(): ?string
    {
        return $this->seo_meta_description ?: $this->excerpt;
    }
}
```

---

## 3. Сидер категорий

**Файл:** `src/new-kratom/app/database/seeders/WikiCategoriesSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\WikiCategory;
use Illuminate\Database\Seeder;

class WikiCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'slug' => 'botanika-a-veda',
                'title' => 'Botanika a věda',
                'eyebrow' => 'Rostlina a chemie',
                'description' => 'Mitragyna speciosa jako rostlina, její alkaloidní profil, fermentace a vědecký pohled na chemii kratomu.',
                'icon' => 'leaf',
                'accent' => 'grass',
                'position' => 10,
            ],
            [
                'slug' => 'historie-a-kultura',
                'title' => 'Historie a kultura',
                'eyebrow' => 'Etnobotanika',
                'description' => 'Tradiční použití kratomu v jihovýchodní Asii, historický kontext a regionální rozdíly.',
                'icon' => 'book-open',
                'accent' => 'amber',
                'position' => 20,
            ],
            [
                'slug' => 'legislativa-cr',
                'title' => 'Legislativa ČR',
                'eyebrow' => 'Právní rámec 2026',
                'description' => 'Aktuální regulace kratomu v České republice po novele 167/1998 Sb., role státních orgánů a srovnání s EU.',
                'icon' => 'gavel',
                'accent' => 'terra',
                'position' => 30,
            ],
            [
                'slug' => 'kvalita-a-bezpecnost',
                'title' => 'Kvalita a bezpečnost',
                'eyebrow' => 'Laboratoř a normy',
                'description' => 'Jak číst Certificate of Analysis, limity těžkých kovů a mykotoxinů, metody HPLC a ICP-MS, skladování.',
                'icon' => 'flask-conical',
                'accent' => 'cream',
                'position' => 40,
            ],
        ];

        foreach ($rows as $row) {
            WikiCategory::updateOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
```

---

## 4. Подключение в `DatabaseSeeder`

В файле `src/new-kratom/app/database/seeders/DatabaseSeeder.php` найди
секцию, где вызываются другие сидеры (`LabBatchesSeeder`, `ForumDemoSeeder`),
и добавь:

```php
$this->call(WikiCategoriesSeeder::class);
// контентные сидеры (фазы 05–07) добавятся позже:
// $this->call(WikiContentVedaSeeder::class);
// $this->call(WikiContentHistoriePravoSeeder::class);
// $this->call(WikiContentKvalitaSeeder::class);
```

---

## 5. Проверка (Definition of Done)

```bash
cd src/new-kratom/app
php artisan migrate
php artisan db:seed --class=WikiCategoriesSeeder

php artisan tinker --execute='echo App\Models\WikiCategory::active()->pluck("slug")->implode(", ");'
# Ожидаем: botanika-a-veda, historie-a-kultura, legislativa-cr, kvalita-a-bezpecnost
```

После этой фазы — **остановись и закоммить**:
```
git add -A && git commit -m "pruvodce-phase-01: wiki tables + models + categories seeder"
```

Потом запускай PHASE-02.
