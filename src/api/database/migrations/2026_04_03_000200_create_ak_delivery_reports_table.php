<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ak_delivery_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 32)->default('messenger');
            $table->string('order_number', 64);
            $table->string('recipient_fullname');
            $table->string('recipient_actual_fullname')->nullable();
            $table->string('id_card_number', 100);
            $table->string('id_card_type', 32);
            $table->string('handover_place');
            $table->timestamp('handover_datetime');
            $table->string('sender_fullname');
            $table->longText('customer_signature')->nullable();
            $table->longText('seller_signature')->nullable();
            $table->json('payload')->nullable();
            $table->boolean('is_test')->default(false);
            $table->boolean('order_found')->default(false);
            $table->boolean('delivery_status_applied')->default(false);
            $table->boolean('pay_status_applied')->default(false);
            $table->boolean('order_status_applied')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'order_number'], 'ak_delivery_reports_provider_order_number_unique');
            $table->index(['provider', 'created_at'], 'ak_delivery_reports_provider_created_at_index');
            $table->index('order_number', 'ak_delivery_reports_order_number_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ak_delivery_reports');
    }
};
