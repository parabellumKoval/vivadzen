<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Лабораторные тесты (COA / Šarže).
 *
 * Партии (lot) тестируются отдельно, поэтому хранятся как самостоятельные
 * сущности. Одна партия может покрывать несколько товаров (например, та же
 * партия prášku фасуется как "Bílý Slon" и "Zelený Rurut Nano").
 *
 * tests — структурированные измерения, сгруппированные по разделам:
 *   active | metals | mycotoxins | pah | microbiology
 *   каждая запись — { name, value, uncertainty?, below_loq?, unit, limit, status }
 *
 * PDF-протоколы хранятся в отдельной таблице (несколько на партию).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_batches', function (Blueprint $table) {
            $table->id();
            $table->string('lot', 64)->unique();
            $table->string('product_name')->nullable();
            $table->json('strains')->nullable();        // ["Bílý Slon", "Zelený Rurut Nano"]
            $table->string('package')->nullable();      // "Doypack ZIP, PE sáček"
            $table->string('mass', 32)->nullable();     // "400 g"
            $table->string('lab_name')->default('VŠCHT Praha');
            $table->date('received_at')->nullable();
            $table->date('issued_at')->nullable();
            $table->json('tests')->nullable();          // { active: [...], metals: [...], ... }
            $table->json('summary')->nullable();        // { total, passed, ratio, mitragynin, h7mg }
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->index('issued_at');
        });

        Schema::create('lab_batch_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_batch_id')->constrained()->cascadeOnDelete();
            $table->string('disk', 32)->default('public');
            $table->string('path');
            $table->string('url', 512)->nullable();
            $table->string('original_name')->nullable();
            $table->string('file_no', 64)->nullable();   // "ML 63/26"
            $table->string('label')->nullable();         // "Aktivní látky + těžké kovy"
            $table->date('tested_at')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['lab_batch_id', 'position']);
        });

        Schema::create('lab_batch_product', function (Blueprint $table) {
            $table->foreignId('lab_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['lab_batch_id', 'product_id']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_batch_product');
        Schema::dropIfExists('lab_batch_files');
        Schema::dropIfExists('lab_batches');
    }
};
