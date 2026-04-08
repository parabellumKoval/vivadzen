<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ak_tg_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('telegram_user_id')->unique();
            $table->string('username', 255)->nullable();
            $table->string('first_name', 150)->nullable();
            $table->string('last_name', 150)->nullable();
            $table->string('phone', 80)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('avatar_url', 1024)->nullable();
            $table->string('language_code', 16)->nullable();
            $table->string('payment_method', 120)->nullable();
            $table->json('addresses')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ak_tg_profiles');
    }
};
