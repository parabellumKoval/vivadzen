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
            $table->string('eyebrow', 120)->nullable();
            $table->text('description')->nullable();
            $table->string('icon', 32)->nullable();
            $table->string('accent', 16)->default('grass');
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
            $table->string('excerpt', 320)->nullable();
            $table->longText('body');

            $table->string('cover_path')->nullable();
            $table->string('cover_url')->nullable();
            $table->string('cover_alt')->nullable();

            $table->string('seo_keyword', 160)->nullable();
            $table->json('seo_secondary_keywords')->nullable();
            $table->string('seo_search_intent', 32)->default('informational');
            $table->unsignedInteger('seo_volume_estimate')->nullable();
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
