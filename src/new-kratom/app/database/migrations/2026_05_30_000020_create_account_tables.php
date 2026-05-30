<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer accounts: extends the base users table (profile, avatar,
 * social-only users have no password), adds linked social accounts,
 * saved delivery addresses, and back-references from orders/reviews.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Social-only users (Google/Facebook) have no password until they "set" one.
            $table->string('password')->nullable()->change();
            $table->string('phone', 64)->nullable()->after('email');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->boolean('marketing_consent')->default(false)->after('avatar_path');
            $table->string('locale', 8)->nullable()->after('marketing_consent');
            $table->timestamp('blocked_at')->nullable()->after('locale');
        });

        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 32);          // google | facebook
            $table->string('provider_id');
            $table->string('avatar')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_id']);
            $table->index('user_id');
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 64)->nullable();        // "Domů", "Práce"…
            $table->string('first_name', 64);
            $table->string('last_name', 64);
            $table->string('phone', 64)->nullable();
            $table->string('street', 128);
            $table->string('city', 64);
            $table->string('zip', 16);
            $table->string('country', 64)->default('CZ');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'is_default']);
        });

        // SQLite cannot add FK constraints to existing tables via ALTER,
        // so we only add an indexed column and rely on app-level relations.
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('product_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });

        Schema::dropIfExists('addresses');
        Schema::dropIfExists('social_accounts');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'avatar_path', 'marketing_consent', 'locale', 'blocked_at']);
        });
    }
};
