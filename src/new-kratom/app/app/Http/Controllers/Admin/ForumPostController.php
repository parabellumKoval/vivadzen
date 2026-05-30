<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForumPost;
use App\Models\ForumTopic;
use App\Models\User;
use App\Support\Forum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ForumPostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ForumPost::query()
            ->with(['author:id,name,email,forum_slug', 'topic:id,title,slug,emoji,status']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($topicId = $request->integer('topic_id')) {
            $query->where('forum_topic_id', $topicId);
        }

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('body', 'like', "%{$search}%")
                    ->orWhereHas('author', fn ($aq) => $aq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('topic', fn ($tq) => $tq->where('title', 'like', "%{$search}%"));
            });
        }

        $posts = $query->latest()->paginate($request->integer('per_page', 25));
        $posts->getCollection()->transform(fn (ForumPost $post) => $this->shape($post));

        return response()->json(['data' => $posts]);
    }

    public function show(ForumPost $post): JsonResponse
    {
        return response()->json([
            'data' => $this->shape($post->load(['author:id,name,email,forum_slug', 'topic:id,title,slug,emoji,status'])),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'forum_topic_id' => 'required|integer|exists:forum_topics,id',
            'user_id' => 'required|integer|exists:users,id',
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('forum_posts', 'id')
                    ->where(fn ($query) => $query->where('forum_topic_id', $request->input('forum_topic_id'))),
            ],
            'body' => 'required|string|min:2|max:8000',
            'status' => 'required|in:pending,approved,rejected',
            'moderation_note' => 'nullable|string|max:4000',
            'score' => 'nullable|integer',
            'published_at' => 'nullable|date',
        ]);

        $topic = ForumTopic::findOrFail($data['forum_topic_id']);
        $user = Forum::ensureUserProfile(User::findOrFail($data['user_id']));

        if ($data['status'] === 'approved' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if ($data['status'] === 'rejected') {
            $data['published_at'] = null;
        }

        $post = ForumPost::create([
            'forum_topic_id' => $topic->id,
            'user_id' => $user->id,
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $data['body'],
            'status' => $data['status'],
            'moderation_note' => $data['moderation_note'] ?? null,
            'score' => (int) ($data['score'] ?? 0),
            'published_at' => $data['published_at'] ?? null,
        ]);

        if ($post->status === 'approved') {
            $topic->forceFill([
                'last_post_at' => $post->published_at ?? $post->created_at,
                'last_post_user_id' => $user->id,
            ])->save();
        }

        return response()->json([
            'data' => $this->shape($post->fresh(['author:id,name,email,forum_slug', 'topic:id,title,slug,emoji,status'])),
        ], 201);
    }

    public function update(Request $request, ForumPost $post): JsonResponse
    {
        $data = $request->validate([
            'body' => 'required|string|min:2|max:8000',
            'status' => 'required|in:pending,approved,rejected',
            'moderation_note' => 'nullable|string|max:4000',
            'score' => 'nullable|integer',
            'published_at' => 'nullable|date',
        ]);

        if ($data['status'] === 'approved' && empty($data['published_at'])) {
            $data['published_at'] = $post->published_at ?? now();
        }

        if ($data['status'] === 'rejected') {
            $data['published_at'] = null;
        }

        $post->update($data);

        return response()->json([
            'data' => $this->shape($post->fresh(['author:id,name,email,forum_slug', 'topic:id,title,slug,emoji,status'])),
        ]);
    }

    public function destroy(ForumPost $post): JsonResponse
    {
        $post->delete();

        return response()->json(['ok' => true]);
    }

    public function approve(ForumPost $post): JsonResponse
    {
        $post->update([
            'status' => 'approved',
            'published_at' => $post->published_at ?? now(),
        ]);

        return response()->json(['data' => $this->shape($post->fresh(['author:id,name,email,forum_slug', 'topic:id,title,slug,emoji,status']))]);
    }

    public function reject(Request $request, ForumPost $post): JsonResponse
    {
        $post->update([
            'status' => 'rejected',
            'published_at' => null,
            'moderation_note' => $request->input('moderation_note'),
        ]);

        return response()->json(['data' => $this->shape($post->fresh(['author:id,name,email,forum_slug', 'topic:id,title,slug,emoji,status']))]);
    }

    private function shape(ForumPost $post): array
    {
        return [
            'id' => $post->id,
            'forum_topic_id' => $post->forum_topic_id,
            'parent_id' => $post->parent_id,
            'body' => $post->body,
            'status' => $post->status,
            'moderation_note' => $post->moderation_note,
            'score' => $post->score,
            'published_at' => $post->published_at,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
            'author' => $post->author,
            'topic' => $post->topic,
        ];
    }
}
