<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regions', function (Blueprint $table) {
          $table->id();

          $table->json('name', 255);
          $table->string('slug', 255);
          $table->longtext('content')->nullable();
          
          $table->boolean('is_active')->default(1);
          
          $table->foreignId('category_id')->default(null)->nullable();

          $table->json('seo')->nullable();
          $table->json('extras')->nullable();
          $table->json('extras_trans')->nullable();
          
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
        Schema::dropIfExists('regions');
    }
}
