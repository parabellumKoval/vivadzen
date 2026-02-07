<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ak_scheduled_publications', function (Blueprint $table) {
            $table->id();
            
            // Полиморфная связь с моделью
            $table->morphs('schedulable');
            
            // Дата и время запланированной публикации
            $table->timestamp('publish_at')->index();
            
            // Перезаписать created_at при публикации
            $table->boolean('overwrite_created_at')->default(false);
            
            // Поле модели, которое нужно переключить
            $table->string('publish_field')->default('is_published');
            
            // Значение, которое нужно установить (обычно true/1)
            $table->string('publish_value')->default('1');
            
            // Статус: pending, published, cancelled
            $table->enum('status', ['pending', 'published', 'cancelled'])->default('pending')->index();
            
            // Когда была фактически выполнена публикация
            $table->timestamp('published_at')->nullable();
            
            // Дополнительные метаданные (JSON)
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Индекс для быстрого поиска ожидающих публикаций
            $table->index(['status', 'publish_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ak_scheduled_publications');
    }
};
