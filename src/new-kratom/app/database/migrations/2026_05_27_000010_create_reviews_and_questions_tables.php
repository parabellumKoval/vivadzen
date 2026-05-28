<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Отзывы покупателей + вопросы и ответы.
 *
 * Менеджер может сам писать отзывы, в том числе задним числом
 * (published_at в прошлом) или запланировать на будущее (в будущем).
 * Покупатель прикладывает до 3 фото — храним в review_images.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('author_name', 120);
            $table->string('author_email', 190)->nullable();
            $table->unsignedTinyInteger('rating');
            $table->string('package', 32)->nullable();
            $table->text('body');
            $table->boolean('verified_purchase')->default(false);
            $table->unsignedInteger('helpful_count')->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedBigInteger('created_by_admin_id')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status', 'published_at']);
        });

        Schema::create('product_review_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_review_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['product_review_id', 'position']);
        });

        Schema::create('product_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('author_name', 120);
            $table->string('author_email', 190)->nullable();
            $table->text('question');
            $table->text('answer')->nullable();
            $table->string('answered_by', 120)->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->unsignedInteger('helpful_count')->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedBigInteger('created_by_admin_id')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_questions');
        Schema::dropIfExists('product_review_images');
        Schema::dropIfExists('product_reviews');
    }
};
