<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configurable delivery and payment methods. Codes are stable identifiers
 * (referenced in checkout/orders), other fields are admin-editable: price,
 * ETA, address (for pickup), and the boolean active flag. Payment methods
 * may opt out of specific delivery types via a JSON whitelist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();             // pickup_praha_1, messanger, messanger_express
            $table->string('type', 32);                       // pickup | courier
            $table->json('name');                             // localized {cs,en,ru,uk}
            $table->json('description')->nullable();
            $table->json('eta')->nullable();                  // "do 2 hodin" / "1–2 dny"
            $table->json('address')->nullable();              // for pickup: {street, city, zip, hours}
            $table->unsignedInteger('price')->default(0);     // Kč
            $table->unsignedInteger('free_above')->nullable();// free shipping threshold
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();             // cod_messanger, qr, bank_transfer, niftipay
            $table->string('type', 32);                       // cod | qr | bank | online
            $table->json('name');
            $table->json('description')->nullable();
            $table->unsignedInteger('fee')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            // Codes of delivery methods this payment is compatible with;
            // empty/null = compatible with everything.
            $table->json('delivery_method_codes')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('delivery_methods');
    }
};
