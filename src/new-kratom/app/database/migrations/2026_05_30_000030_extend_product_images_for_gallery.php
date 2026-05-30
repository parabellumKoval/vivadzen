<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Расширяем product_images для полноценной галереи:
 *  - title — отдельно от alt (для tooltip/captions)
 *  - disk  — где лежит оригинал (public | bunny | s3 …)
 *  - url   — кэш готового URL (для bunny — абсолютный CDN-URL,
 *            для local — публичный путь /storage/...)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            if (! Schema::hasColumn('product_images', 'title')) {
                $table->string('title')->nullable()->after('alt');
            }
            if (! Schema::hasColumn('product_images', 'disk')) {
                $table->string('disk', 32)->default('public')->after('product_id');
            }
            if (! Schema::hasColumn('product_images', 'url')) {
                $table->string('url', 512)->nullable()->after('path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            foreach (['title', 'disk', 'url'] as $col) {
                if (Schema::hasColumn('product_images', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
