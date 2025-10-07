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
      Schema::create('ak_admin_users', function (Blueprint $t) {
          $t->id();
          $t->string('name');
          $t->string('email')->unique();
          $t->string('password');
          $t->rememberToken();
          $t->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ak_admin_users');
    }
};
