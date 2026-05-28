<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Заказы. Создаются асинхронно из CreateOrderJob (фронт диспатчит,
 * worker пишет). public_id (VZ-2026-NNNN) генерируется в job и
 * возвращается на фронт по polling/WebSocket.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 32)->unique(); // VZ-2026-0001
            $table->string('status', 32)->default('pending');
            // received → paid → packed → shipped → delivered | cancelled | refunded
            $table->string('email');
            $table->string('phone', 64);
            $table->string('first_name', 64);
            $table->string('last_name', 64);
            $table->string('street', 128);
            $table->string('city', 64);
            $table->string('zip', 16);
            $table->string('country', 64)->default('CZ');
            $table->string('delivery_method', 32);
            $table->string('payment_method', 32);
            $table->string('payment_status', 32)->default('unpaid');
            $table->string('promo_code', 64)->nullable();
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('shipping')->default(0);
            $table->unsignedInteger('total');
            $table->unsignedInteger('items_count');
            $table->string('locale', 8)->default('cs');
            $table->boolean('marketing_consent')->default(false);
            $table->text('note')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index('email');
            $table->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('product_slug', 128);
            $table->string('product_name', 255);
            $table->unsignedInteger('size');
            $table->string('unit', 8)->default('g');
            $table->unsignedInteger('qty');
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('line_total');
            // snapshot, чтобы исторический заказ не "поплыл" после изменения товара
            $table->json('snapshot')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'product_slug']);
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32);
            $table->text('note')->nullable();
            $table->foreignId('admin_user_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
