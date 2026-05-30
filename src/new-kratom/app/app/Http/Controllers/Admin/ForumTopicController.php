<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use App\Models\ForumTopic;
use App\Models\User;
use App\Support\Forum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ForumTopicController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ForumTopic::query()
            ->with(['author:id,name,email,forum_slug', 'category:id,slug,label,icon'])
            ->withCount('approvedPosts');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($category = $request->query('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        $topics = $query
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_post_at')
            ->paginate($request->integer('per_page', 25));

        $topics->getCollection()->transform(fn (ForumTopic $topic) => $this->shape($topic));

        return response()->json(['data' => $topics]);
    }

    public function show(ForumTopic $topic): JsonResponse
    {
        return response()->json([
            'data' => $this->shape($topic->load(['author:id,name,email,forum_slug', 'category:id,slug,label,icon'])->loadCount('approvedPosts')),
            'posts' => $topic->posts()
                ->with('author:id,name,email,forum_slug')
                ->latest()
                ->limit(100)
                ->get()
                ->map(fn ($post) => [
                    'id' => $post->id,
                    'author' => $post->author,
                    'parent_id' => $post->parent_id,
                    'body' => $post->body,
                    'status' => $post->status,
                    'score' => $post->score,
                    'created_at' => $post->created_at,
                ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'forum_category_id' => 'nullable|integer|exists:forum_categories,id',
            'title' => 'required|string|max:160',
            'emoji' => ['required', 'string', 'max:16', Rule::in(Forum::topicEmojis())],
            'body' => 'required|string|min:10|max:12000',
            'status' => 'required|in:pending,approved,rejected',
            'moderation_note' => 'nullable|string|max:4000',
            'is_pinned' => 'sometimes|boolean',
            'is_locked' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'score' => 'nullable|integer',
            'published_at' => 'nullable|date',
        ]);

        $user = Forum::ensureUserProfile(User::findOrFail($data['user_id']));

        if ($data['status'] === 'approved' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if ($data['status'] === 'rejected') {
            $data['published_at'] = null;
        }

        $topic = ForumTopic::create([
            'user_id' => $user->id,
            'forum_category_id' => $data['forum_category_id'] ?? null,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['title']),
            'emoji' => $data['emoji'],
            'body' => $data['body'],
            'status' => $data['status'],
            'moderation_note' => $data['moderation_note'] ?? null,
            'is_pinned' => (bool) ($data['is_pinned'] ?? false),
            'is_locked' => (bool) ($data['is_locked'] ?? false),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'score' => (int) ($data['score'] ?? 0),
            'published_at' => $data['published_at'] ?? null,
            'last_post_at' => $data['published_at'] ?? now(),
            'last_post_user_id' => $user->id,
        ]);

        return response()->json([
            'data' => $this->shape($topic->fresh(['author:id,name,email,forum_slug', 'category:id,slug,label,icon'])->loadCount('approvedPosts')),
        ], 201);
    }

    public function update(Request $request, ForumTopic $topic): JsonResponse
    {
        $data = $request->validate([
            'forum_category_id' => 'nullable|integer|exists:forum_categories,id',
            'title' => 'required|string|max:160',
            'emoji' => ['required', 'string', 'max:16', Rule::in(Forum::topicEmojis())],
            'body' => 'required|string|min:10|max:12000',
            'status' => 'required|in:pending,approved,rejected',
            'moderation_note' => 'nullable|string|max:4000',
            'is_pinned' => 'sometimes|boolean',
            'is_locked' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'score' => 'nullable|integer',
            'published_at' => 'nullable|date',
        ]);

        if (($data['status'] ?? null) === 'approved' && empty($data['published_at'])) {
            $data['published_at'] = $topic->published_at ?? now();
        }

        if (($data['status'] ?? null) === 'rejected') {
            $data['published_at'] = null;
        }

        $topic->update($data);

        return response()->json([
            'data' => $this->shape($topic->fresh(['author:id,name,email,forum_slug', 'category:id,slug,label,icon'])->loadCount('approvedPosts')),
        ]);
    }

    public function destroy(ForumTopic $topic): JsonResponse
    {
        $topic->delete();

        return response()->json(['ok' => true]);
    }

    public function approve(ForumTopic $topic): JsonResponse
    {
        $topic->update([
            'status' => 'approved',
            'published_at' => $topic->published_at ?? now(),
        ]);

        return response()->json(['data' => $this->shape($topic->fresh(['author:id,name,email,forum_slug', 'category:id,slug,label,icon'])->loadCount('approvedPosts'))]);
    }

    public function reject(Request $request, ForumTopic $topic): JsonResponse
    {
        $topic->update([
            'status' => 'rejected',
            'published_at' => null,
            'moderation_note' => $request->input('moderation_note'),
        ]);

        return response()->json(['data' => $this->shape($topic->fresh(['author:id,name,email,forum_slug', 'category:id,slug,label,icon'])->loadCount('approvedPosts'))]);
    }

    private function shape(ForumTopic $topic): array
    {
        return [
            'id' => $topic->id,
            'title' => $topic->title,
            'slug' => $topic->slug,
            'emoji' => $topic->emoji,
            'body' => $topic->body,
            'status' => $topic->status,
            'moderation_note' => $topic->moderation_note,
            'is_pinned' => $topic->is_pinned,
            'is_locked' => $topic->is_locked,
            'is_featured' => $topic->is_featured,
            'views_count' => $topic->views_count,
            'score' => $topic->score,
            'published_at' => $topic->published_at,
            'last_post_at' => $topic->last_post_at,
            'created_at' => $topic->created_at,
            'updated_at' => $topic->updated_at,
            'approved_posts_count' => $topic->approved_posts_count ?? null,
            'cover_url' => $topic->coverUrl(),
            'cover_credit' => $topic->cover_credit,
            'author' => $topic->author,
            'category' => $topic->category,
            'forum_category_id' => $topic->forum_category_id,
        ];
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'tema';
        $slug = $base;
        $suffix = 2;

        while (ForumTopic::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
