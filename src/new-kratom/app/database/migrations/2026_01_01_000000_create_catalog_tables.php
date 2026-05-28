<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Каталог: taxonomies (color/strain/form/region) + products + variants
 * + images + COA. Все label/description/title — JSON-локализованные
 * (cs/en/ru/uk), чтобы не плодить *_translations таблицы.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxonomies', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['color', 'strain', 'form', 'region']);
            $table->string('slug', 64);
            $table->json('label');           // {cs: "...", en: "...", ru: "...", uk: "..."}
            $table->json('description')->nullable();
            $table->json('meta')->nullable(); // accent, vein, h1, origin, range_min/max, dose, comingSoon
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['type', 'slug']);
            $table->index(['type', 'position']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 128)->unique();
            $table->json('name');             // {cs, en, ru, uk}
            $table->json('short')->nullable();
            $table->json('description')->nullable();
            // Главные таксономии (быстрые фильтры без JOIN)
            $table->string('color_slug', 32)->nullable()->index();
            $table->string('strain_slug', 32)->nullable()->index();
            $table->string('form_slug', 32)->nullable()->index();
            $table->string('origin', 128)->nullable();
            // Лабораторные показатели
            $table->decimal('mitragynin', 5, 2)->nullable();
            $table->decimal('h7mg', 5, 3)->nullable();
            $table->decimal('purity', 5, 2)->nullable();
            $table->string('batch', 64)->nullable();
            $table->date('tested_at')->nullable();
            // UI/маркетинг
            $table->string('grind', 64)->nullable();
            $table->decimal('rating', 3, 1)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('questions_count')->default(0);
            $table->boolean('in_stock')->default(true);
            $table->json('badge')->nullable(); // {variant, label}
            $table->string('main_image')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->index(['in_stock', 'published_at']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('size');         // 25, 50, 10 (для extract)
            $table->string('unit', 8)->default('g'); // g | ml
            $table->unsignedInteger('price');         // в Kč, копейки не нужны
            $table->unsignedInteger('compare_price')->nullable();
            $table->string('sku', 64)->nullable();
            $table->integer('stock')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'size', 'unit']);
        });

        // Связь product ↔ taxonomy (для мульти-тегов: например один продукт
        // одновременно в "borneo" регион и "rurut" штамме). Главные связки
        // дублируются в products.{color|strain|form}_slug для скорости.
        Schema::create('product_taxonomy', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('taxonomy_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'taxonomy_id']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt')->nullable();
            // Сгенерированные ImageOptimizer сорты для srcset:
            // {avif: {640, 960, 1280}, webp: {...}, jpeg: {...}}
            $table->json('renditions')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['product_id', 'position']);
        });

        Schema::create('coa_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('batch', 64);
            $table->date('tested_at');
            $table->string('lab_name')->default('VŠCHT Praha');
            $table->decimal('mitragynin', 5, 2)->nullable();
            $table->decimal('h7mg', 5, 3)->nullable();
            $table->decimal('purity', 5, 2)->nullable();
            $table->string('pdf_path')->nullable();
            $table->json('summary')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'batch']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coa_files');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_taxonomy');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('taxonomies');
    }
};
