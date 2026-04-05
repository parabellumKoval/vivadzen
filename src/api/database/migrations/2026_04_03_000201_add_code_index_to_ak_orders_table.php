<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ak_orders', function (Blueprint $table): void {
            $table->index('code', 'ak_orders_code_index');
        });
    }

    public function down(): void
    {
        Schema::table('ak_orders', function (Blueprint $table): void {
            $table->dropIndex('ak_orders_code_index');
        });
    }
};
