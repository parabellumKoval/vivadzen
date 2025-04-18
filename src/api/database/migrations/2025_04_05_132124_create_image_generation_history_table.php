<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImageGenerationHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('image_generation_history', function (Blueprint $table) {
          $table->id();
          $table->morphs('generatable');
          $table->enum('status', ['pending', 'done', 'error'])->default('pending');
          $table->json('extras')->nullable();
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
        Schema::dropIfExists('image_generation_history');
    }
}
