<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('feeds', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('key')->unique();
        $table->text('content')->nullable();
        $table->boolean('is_active')->default(1);
        $table->json('settings')->nullable();
        $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('feeds');
    }
}
