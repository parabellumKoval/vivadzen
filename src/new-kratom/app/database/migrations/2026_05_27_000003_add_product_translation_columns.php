<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('origin_i18n')->nullable()->after('origin');
            $table->json('grind_i18n')->nullable()->after('grind');
        });

        DB::table('products')
            ->select(['id', 'origin', 'grind'])
            ->orderBy('id')
            ->get()
            ->each(function (object $product): void {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'origin_i18n' => $product->origin ? json_encode(['cs' => $product->origin], JSON_UNESCAPED_UNICODE) : null,
                        'grind_i18n' => $product->grind ? json_encode(['cs' => $product->grind], JSON_UNESCAPED_UNICODE) : null,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['origin_i18n', 'grind_i18n']);
        });
    }
};
