<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('forum_slug')->nullable()->unique()->after('locale');
            $table->string('forum_signature', 220)->nullable()->after('forum_slug');
            $table->unsignedInteger('forum_reputation')->default(0)->after('forum_signature');
            $table->string('forum_avatar_color', 16)->nullable()->after('forum_reputation');
        });

        Schema::create('forum_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->string('label', 120);
            $table->string('icon', 16)->default('💬');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('forum_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('forum_category_id')->nullable()->constrained('forum_categories')->nullOnDelete();
            $table->string('title', 160);
            $table->string('slug', 180)->unique();
            $table->string('emoji', 16)->default('💬');
            $table->longText('body');
            $table->string('cover_path')->nullable();
            $table->string('cover_url')->nullable();
            $table->string('cover_source_url')->nullable();
            $table->string('cover_credit')->nullable();
            $table->string('status', 24)->default('approved')->index();
            $table->text('moderation_note')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->integer('score')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('last_post_at')->nullable()->index();
            $table->foreignId('last_post_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['forum_category_id', 'status', 'last_post_at']);
            $table->index(['is_pinned', 'is_featured']);
        });

        Schema::create('forum_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_topic_id')->constrained('forum_topics')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('forum_posts')->cascadeOnDelete();
            $table->longText('body');
            $table->string('status', 24)->default('approved')->index();
            $table->text('moderation_note')->nullable();
            $table->integer('score')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->index(['forum_topic_id', 'status', 'created_at']);
            $table->index(['user_id', 'status', 'created_at']);
        });

        Schema::create('forum_post_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_post_id')->constrained('forum_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('value');
            $table->timestamps();

            $table->unique(['forum_post_id', 'user_id']);
        });

        Schema::create('forum_post_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_post_id')->constrained('forum_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('emoji', 16);
            $table->timestamps();

            $table->unique(['forum_post_id', 'user_id', 'emoji']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_post_reactions');
        Schema::dropIfExists('forum_post_votes');
        Schema::dropIfExists('forum_posts');
        Schema::dropIfExists('forum_topics');
        Schema::dropIfExists('forum_categories');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['forum_slug']);
            $table->dropColumn([
                'forum_slug',
                'forum_signature',
                'forum_reputation',
                'forum_avatar_color',
            ]);
        });
    }
};
