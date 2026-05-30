<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Отзыв не зависит от модификации (вес/упаковка): убираем поле `package`.
 * Отзыв привязан только к товару (product_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('product_reviews', 'package')) {
                $table->dropColumn('package');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('product_reviews', 'package')) {
                $table->string('package', 32)->nullable()->after('rating');
            }
        });
    }
};
