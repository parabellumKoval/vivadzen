<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Address form is being trimmed: country is always CZ, label / first / last
 * name and ZIP are dropped, and the free-text "city" column is replaced by
 * a foreign key to the canonical cities catalogue.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('user_id')->constrained('cities')->nullOnDelete();
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['label', 'first_name', 'last_name', 'zip', 'country', 'city']);
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('label', 64)->nullable();
            $table->string('first_name', 64)->default('');
            $table->string('last_name', 64)->default('');
            $table->string('city', 64)->default('');
            $table->string('zip', 16)->default('');
            $table->string('country', 64)->default('CZ');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('city_id');
        });
    }
};
