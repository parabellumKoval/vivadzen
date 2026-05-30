<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reference catalogue of Czech populated places (GeoNames feature class "P").
 * Used by the address picker on the customer account and checkout: users
 * select from the official list so we avoid ambiguous free-text city values.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('geonames_id')->unique();
            $table->string('name', 200);
            $table->string('ascii_name', 200)->index();
            $table->string('region_name', 200)->nullable();   // kraj (admin1)
            $table->string('region_code', 10)->nullable();
            $table->string('district_name', 200)->nullable(); // okres (admin2)
            $table->string('district_code', 16)->nullable();
            $table->string('feature_code', 16)->nullable();   // PPL / PPLA / PPLA2 / PPLC / …
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->unsignedInteger('population')->default(0);
            $table->timestamps();

            $table->index(['name']);
            $table->index(['district_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
