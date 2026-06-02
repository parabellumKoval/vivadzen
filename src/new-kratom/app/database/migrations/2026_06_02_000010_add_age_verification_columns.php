<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // When TRUE the customer is exempt from ADULTO age verification at checkout
            // (e.g. verified once offline, store owner whitelisted them in the admin).
            $table->boolean('age_verification_skipped')->default(false)->after('blocked_at');
        });

        Schema::table('orders', function (Blueprint $table) {
            // UID returned by the ADULTO widget. Empty if the order was placed by a
            // customer with age_verification_skipped = true, or before the feature was
            // wired up.
            $table->string('age_verification_uid', 128)->nullable()->after('marketing_consent');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('age_verification_skipped');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('age_verification_uid');
        });
    }
};
