<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('np_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 36)->unique();
            $table->string('name_uk')->nullable();
            $table->string('name_ru')->nullable();
            $table->string('area_uk')->nullable();
            $table->string('area_ru')->nullable();
            $table->string('region_uk')->nullable();
            $table->string('region_ru')->nullable();
            $table->string('type_uk')->nullable();
            $table->string('type_ru')->nullable();
            $table->unsignedSmallInteger('popular_rank')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('np_settlements');
    }
};
