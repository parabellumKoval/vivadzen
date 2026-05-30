<?php

namespace App\Support;

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumTopic;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Forum
{
    public static function levels(): array
    {
        return [
            'sprout' => ['name' => 'Klíček', 'color' => 'var(--c-grass-300)', 'icon' => '🌱', 'min' => 0],
            'leaf' => ['name' => 'Zelený list', 'color' => 'var(--c-grass-500)', 'icon' => '🍃', 'min' => 50],
            'knower' => ['name' => 'Znalec', 'color' => 'var(--c-amber-500)', 'icon' => '⭐', 'min' => 250],
            'master' => ['name' => 'Mistr', 'color' => 'var(--c-terracotta-500)', 'icon' => '🔥', 'min' => 750],
            'guru' => ['name' => 'Guru', 'color' => 'var(--c-amber-700)', 'icon' => '👑', 'min' => 2000],
        ];
    }

    public static function defaultCategories(): array
    {
        return [
            ['slug' => 'beginners', 'label' => 'Pro začátečníky', 'icon' => '🌱', 'position' => 10],
            ['slug' => 'strains', 'label' => 'Odrůdy', 'icon' => '🌿', 'position' => 20],
            ['slug' => 'effects', 'label' => 'Účinky a zkušenosti', 'icon' => '✨', 'position' => 30],
            ['slug' => 'preparation', 'label' => 'Příprava', 'icon' => '🍵', 'position' => 40],
            ['slug' => 'legal', 'label' => 'Legislativa', 'icon' => '⚖️', 'position' => 50],
            ['slug' => 'community', 'label' => 'Komunita', 'icon' => '🤝', 'position' => 60],
        ];
    }

    public static function categories(): array
    {
        $categories = [
            'all' => ['label' => 'Vše', 'icon' => '💬'],
        ];

        if (! Schema::hasTable('forum_categories')) {
            foreach (self::defaultCategories() as $category) {
                $categories[$category['slug']] = [
                    'label' => $category['label'],
                    'icon' => $category['icon'],
                    'description' => null,
                ];
            }

            return $categories;
        }

        ForumCategory::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('label')
            ->get()
            ->each(function (ForumCategory $category) use (&$categories) {
                $categories[$category->slug] = [
                    'id' => $category->id,
                    'label' => $category->label,
                    'icon' => $category->icon,
                    'description' => $category->description,
                ];
            });

        if (count($categories) === 1) {
            foreach (self::defaultCategories() as $category) {
                $categories[$category['slug']] = [
                    'label' => $category['label'],
                    'icon' => $category['icon'],
                    'description' => null,
                ];
            }
        }

        return $categories;
    }

    public static function topicEmojis(): array
    {
        return ['🔥', '🌿', '🍃', '☕', '🍵', '🌱', '⭐', '✨', '💬', '❓', '⚖️', '🌍', '🧪', '📚', '💡', '🌅', '🌙', '☀️'];
    }

    public static function reactions(): array
    {
        return ['👍', '❤️', '🔥', '🌿', '🙏', '😂', '🤔'];
    }

    public static function stats(): array
    {
        if (! self::hasForumTables()) {
            return ['topics' => 0, 'posts' => 0, 'members' => 0, 'online' => 1];
        }

        $online = 1;

        try {
            $online = max(
                1,
                (int) DB::table('sessions')
                    ->whereNotNull('user_id')
                    ->where('last_activity', '>=', now()->subMinutes(15)->timestamp)
                    ->distinct()
                    ->count('user_id')
            );
        } catch (\Throwable) {
            $online = 1;
        }

        return [
            'topics' => ForumTopic::query()->public()->count(),
            'posts' => ForumPost::query()->public()->count(),
            'members' => User::query()
                ->whereHas('forumTopics')
                ->orWhereHas('forumPosts')
                ->count(),
            'online' => $online,
        ];
    }

    public static function users(): array
    {
        if (! self::hasForumTables()) {
            return [];
        }

        return User::query()
            ->whereHas('forumTopics')
            ->orWhereHas('forumPosts')
            ->get()
            ->mapWithKeys(fn (User $user) => [$user->id => self::userPayload($user)])
            ->all();
    }

    public static function userById(?int $id): ?array
    {
        if (! $id) {
            return null;
        }

        $user = User::find($id);

        return $user ? self::userPayload($user) : null;
    }

    public static function userBySlug(string $slug): ?User
    {
        if (! self::hasForumTables()) {
            return null;
        }

        return User::query()
            ->where('forum_slug', $slug)
            ->first();
    }

    public static function currentUserPayload(?User $user): ?array
    {
        return $user ? self::userPayload($user) : null;
    }

    public static function topics(): array
    {
        if (! self::hasForumTables()) {
            return [];
        }

        return self::topicQuery()
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_post_at')
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (ForumTopic $topic) => self::topicPayload($topic))
            ->all();
    }

    public static function featured(int $limit = 3): array
    {
        if (! self::hasForumTables()) {
            return [];
        }

        return self::topicQuery()
            ->where(function ($query) {
                $query->where('is_featured', true)
                    ->orWhereNotNull('cover_path')
                    ->orWhereNotNull('cover_url');
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('score')
            ->orderByDesc('last_post_at')
            ->limit($limit)
            ->get()
            ->map(fn (ForumTopic $topic) => self::topicPayload($topic))
            ->all();
    }

    public static function topTopics(int $limit = 6): array
    {
        if (! self::hasForumTables()) {
            return [];
        }

        return self::topicQuery()
            ->orderByDesc('score')
            ->orderByDesc('approved_posts_count')
            ->orderByDesc('last_post_at')
            ->limit($limit)
            ->get()
            ->map(fn (ForumTopic $topic) => self::topicPayload($topic))
            ->all();
    }

    public static function topicBySlug(string $slug): ?ForumTopic
    {
        if (! self::hasForumTables()) {
            return null;
        }

        return self::topicQuery()
            ->where('slug', $slug)
            ->first();
    }

    public static function commentsForTopic(int $topicId, ?int $currentUserId = null): array
    {
        if (! self::hasForumTables()) {
            return [];
        }

        return ForumPost::query()
            ->public()
            ->where('forum_topic_id', $topicId)
            ->with(['author', 'votes', 'reactions'])
            ->oldest()
            ->get()
            ->map(fn (ForumPost $post) => self::postPayload($post, $currentUserId))
            ->all();
    }

    public static function membersForTopic(ForumTopic $topic): array
    {
        return collect(self::memberIdsForTopic($topic))
            ->map(fn (int $id) => self::userById($id))
            ->filter()
            ->values()
            ->all();
    }

    public static function topicsForUser(User $user): array
    {
        if (! self::hasForumTables()) {
            return [];
        }

        return self::topicQuery()
            ->where('user_id', $user->id)
            ->orderByDesc('last_post_at')
            ->get()
            ->map(fn (ForumTopic $topic) => self::topicPayload($topic))
            ->all();
    }

    public static function postsForUser(User $user, int $limit = 50): array
    {
        if (! self::hasForumTables()) {
            return [];
        }

        return ForumPost::query()
            ->public()
            ->where('user_id', $user->id)
            ->with('topic.category')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (ForumPost $post) => [
                'id' => $post->id,
                'topicSlug' => $post->topic?->slug,
                'topicTitle' => $post->topic?->title,
                'at' => $post->created_at?->toIso8601String(),
                'excerpt' => Str::limit(trim($post->body), 180),
                'score' => $post->score,
            ])
            ->all();
    }

    public static function userPayload(User $user): array
    {
        $reputation = self::reputation($user);
        $topics = $user->forumTopics()->public()->count();
        $posts = $user->forumPosts()->public()->count();

        return [
            'id' => $user->id,
            'slug' => $user->forum_slug ?: self::fallbackSlug($user),
            'name' => $user->name,
            'avatar' => $user->avatar_url,
            'avatarColor' => $user->forum_avatar_color ?: self::colorForUser($user),
            'level' => self::levelKey($reputation),
            'reputation' => $reputation,
            'posts' => $posts,
            'topics' => $topics,
            'joinedAt' => $user->created_at?->toDateString(),
            'bio' => $user->forum_signature,
        ];
    }

    public static function ensureUserProfile(User $user): User
    {
        $dirty = false;

        if (! $user->forum_slug) {
            $user->forum_slug = self::uniqueSlug($user);
            $dirty = true;
        }

        if (! $user->forum_avatar_color) {
            $user->forum_avatar_color = self::colorForUser($user);
            $dirty = true;
        }

        if ($dirty) {
            $user->save();
        }

        return $user;
    }

    public static function levelKey(int $reputation): string
    {
        $current = 'sprout';

        foreach (self::levels() as $key => $level) {
            if ($reputation >= $level['min']) {
                $current = $key;
            }
        }

        return $current;
    }

    public static function topicPayload(ForumTopic $topic): array
    {
        $topic->loadMissing(['author', 'category', 'lastPostUser']);

        $replies = $topic->approved_posts_count ?? $topic->approvedPosts()->count();
        $lastPost = $topic->approvedPosts()->with('author')->latest()->first();
        $lastAt = $lastPost?->created_at ?? $topic->last_post_at ?? $topic->published_at ?? $topic->created_at;
        $lastUserId = $lastPost?->user_id ?? $topic->last_post_user_id ?? $topic->user_id;
        $lastExcerpt = $lastPost ? Str::limit(trim($lastPost->body), 120) : Str::limit(trim($topic->body), 120);
        $coverUrl = $topic->coverUrl();

        return [
            'id' => $topic->id,
            'slug' => $topic->slug,
            'title' => $topic->title,
            'emoji' => $topic->emoji,
            'category' => $topic->category?->slug ?? 'community',
            'cover' => $coverUrl ? 'image' : null,
            'coverUrl' => $coverUrl,
            'coverCredit' => $topic->cover_credit,
            'authorId' => $topic->user_id,
            'createdAt' => ($topic->published_at ?? $topic->created_at)?->toIso8601String(),
            'updatedAt' => $topic->updated_at?->toIso8601String(),
            'body' => $topic->body,
            'replies' => $replies,
            'views' => $topic->views_count,
            'reputation' => $topic->score,
            'isPinned' => $topic->is_pinned,
            'isHot' => $replies >= 8 || $topic->score >= 50,
            'isLocked' => $topic->is_locked,
            'memberIds' => self::memberIdsForTopic($topic),
            'lastReply' => [
                'userId' => $lastUserId,
                'at' => $lastAt instanceof Carbon ? $lastAt->toIso8601String() : Carbon::parse($lastAt)->toIso8601String(),
                'excerpt' => $lastExcerpt,
            ],
        ];
    }

    public static function postPayload(ForumPost $post, ?int $currentUserId = null): array
    {
        $post->loadMissing(['votes', 'reactions']);

        $reactionCounts = $post->reactions
            ->groupBy('emoji')
            ->map(fn ($rows) => $rows->count())
            ->all();

        return [
            'id' => $post->id,
            'parentId' => $post->parent_id,
            'authorId' => $post->user_id,
            'createdAt' => $post->created_at?->toIso8601String(),
            'updatedAt' => $post->updated_at && ! $post->updated_at->equalTo($post->created_at)
                ? $post->updated_at->toIso8601String()
                : null,
            'body' => $post->body,
            'score' => $post->score,
            'reactions' => $reactionCounts,
            'userVote' => $currentUserId
                ? (int) ($post->votes->firstWhere('user_id', $currentUserId)?->value ?? 0)
                : 0,
            'userReactions' => $currentUserId
                ? $post->reactions->where('user_id', $currentUserId)->pluck('emoji')->values()->all()
                : [],
        ];
    }

    private static function topicQuery()
    {
        return ForumTopic::query()
            ->public()
            ->with(['author', 'category', 'lastPostUser'])
            ->withCount(['approvedPosts']);
    }

    private static function hasForumTables(): bool
    {
        return Schema::hasTable('forum_topics')
            && Schema::hasTable('forum_posts')
            && Schema::hasTable('forum_categories');
    }

    private static function memberIdsForTopic(ForumTopic $topic): array
    {
        $ids = ForumPost::query()
            ->where('forum_topic_id', $topic->id)
            ->where('status', 'approved')
            ->distinct()
            ->pluck('user_id')
            ->prepend($topic->user_id)
            ->unique()
            ->values()
            ->all();

        return array_map('intval', $ids);
    }

    private static function reputation(User $user): int
    {
        $topics = $user->forumTopics()->public()->count();
        $posts = $user->forumPosts()->public()->count();
        $topicScore = (int) $user->forumTopics()->public()->sum('score');
        $postScore = (int) $user->forumPosts()->public()->sum('score');

        return max(0, (int) $user->forum_reputation + ($topics * 12) + ($posts * 3) + $topicScore + $postScore);
    }

    private static function fallbackSlug(User $user): string
    {
        return Str::slug($user->name ?: Str::before($user->email, '@')) ?: 'user-' . $user->id;
    }

    private static function uniqueSlug(User $user): string
    {
        $base = self::fallbackSlug($user);
        $slug = $base;
        $i = 2;

        while (User::query()
            ->where('forum_slug', $slug)
            ->whereKeyNot($user->id)
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private static function colorForUser(User $user): string
    {
        $palette = ['#2A5640', '#A8431B', '#3D6F54', '#F4A020', '#6D9482', '#D45C2B', '#4F9E2D', '#142E22'];
        $seed = crc32((string) ($user->email ?: $user->id));

        return $palette[$seed % count($palette)];
    }
}
