<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('np_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 36)->unique();
            $table->string('settlement_ref', 36)->index();
            $table->string('name_uk')->nullable();
            $table->string('name_ru')->nullable();
            $table->string('category')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_postomat')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('np_warehouses');
    }
};
