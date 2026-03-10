<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ak_generation_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 64);
            $table->string('status', 32)->index();
            $table->string('command', 128);
            $table->unsignedBigInteger('initiator_id')->nullable();
            $table->unsignedInteger('progress_total')->default(0);
            $table->unsignedInteger('progress_current')->default(0);
            $table->json('options')->nullable();
            $table->json('meta')->nullable();
            $table->json('result')->nullable();
            $table->longText('output')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ak_generation_runs');
    }
};
