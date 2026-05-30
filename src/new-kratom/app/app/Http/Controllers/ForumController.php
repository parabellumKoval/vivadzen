<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumPostReaction;
use App\Models\ForumPostVote;
use App\Models\ForumTopic;
use App\Support\Forum;
use App\Support\Locale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ForumController extends Controller
{
    public function index(): View
    {
        return view('pages.forum.index', [
            'featured' => Forum::featured(),
            'topTopics' => Forum::topTopics(6),
            'topics' => Forum::topics(),
            'categories' => Forum::categories(),
            'users' => Forum::users(),
            'levels' => Forum::levels(),
            'stats' => Forum::stats(),
        ]);
    }

    public function topic(Request $request, string $slug): View
    {
        $topic = Forum::topicBySlug($slug);
        if (! $topic) {
            abort(404);
        }

        $topic->increment('views_count');
        $topic->refresh();

        $payload = Forum::topicPayload($topic);
        $author = Forum::userById($topic->user_id);
        $currentUser = $request->user()
            ? Forum::currentUserPayload(Forum::ensureUserProfile($request->user()))
            : null;

        return view('pages.forum.topic', [
            'topic' => $payload,
            'topicModel' => $topic,
            'author' => $author,
            'comments' => Forum::commentsForTopic($topic->id, $request->user()?->id),
            'users' => Forum::users(),
            'levels' => Forum::levels(),
            'categories' => Forum::categories(),
            'reactions' => Forum::reactions(),
            'members' => Forum::membersForTopic($topic),
            'currentUser' => $currentUser,
            'endpoints' => [
                'reply' => Locale::url('/forum/tema/' . $topic->id . '/odpovedi'),
                'topicUpdate' => Locale::url('/forum/tema/' . $topic->id),
                'postUpdateBase' => Locale::url('/forum/prispevek'),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $currentUser = $request->user()
            ? Forum::currentUserPayload(Forum::ensureUserProfile($request->user()))
            : null;

        return view('pages.forum.new', [
            'categories' => Forum::categories(),
            'emojis' => Forum::topicEmojis(),
            'currentUser' => $currentUser,
            'storeEndpoint' => Locale::url('/forum/nove-tema'),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $categorySlugs = ForumCategory::query()->where('is_active', true)->pluck('slug')->all();

        $data = $request->validate([
            'title' => 'required|string|min:4|max:160',
            'body' => 'required|string|min:20|max:12000',
            'emoji' => ['nullable', 'string', 'max:16', Rule::in(Forum::topicEmojis())],
            'category' => ['required', 'string', Rule::in($categorySlugs)],
            'cover' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $user = Forum::ensureUserProfile($request->user());
        $category = ForumCategory::where('slug', $data['category'])->firstOrFail();
        $slug = $this->uniqueTopicSlug($data['title']);

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('forum/covers', 'public');
        }

        $topic = ForumTopic::create([
            'user_id' => $user->id,
            'forum_category_id' => $category->id,
            'title' => $data['title'],
            'slug' => $slug,
            'emoji' => $data['emoji'] ?? '💬',
            'body' => $data['body'],
            'cover_path' => $coverPath,
            'status' => 'approved',
            'published_at' => now(),
            'last_post_at' => now(),
            'last_post_user_id' => $user->id,
        ]);

        return response()->json([
            'ok' => true,
            'data' => Forum::topicPayload($topic->fresh(['author', 'category'])),
            'redirect' => Locale::url('/forum/tema/' . $topic->slug),
        ], 201);
    }

    public function storePost(Request $request, ForumTopic $topic): JsonResponse
    {
        $this->assertPublicTopic($topic);

        if ($topic->is_locked) {
            abort(423, 'Diskuze je uzamčená.');
        }

        $data = $request->validate([
            'body' => 'required|string|min:2|max:8000',
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('forum_posts', 'id')->where(fn ($query) => $query->where('forum_topic_id', $topic->id)),
            ],
        ]);

        $user = Forum::ensureUserProfile($request->user());

        $post = ForumPost::create([
            'forum_topic_id' => $topic->id,
            'user_id' => $user->id,
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $data['body'],
            'status' => 'approved',
            'published_at' => now(),
        ]);

        $topic->forceFill([
            'last_post_at' => $post->created_at,
            'last_post_user_id' => $user->id,
        ])->save();

        return response()->json([
            'ok' => true,
            'data' => Forum::postPayload($post->fresh(['votes', 'reactions']), $user->id),
            'topic' => Forum::topicPayload($topic->fresh(['author', 'category', 'lastPostUser'])),
        ], 201);
    }

    public function updateTopic(Request $request, ForumTopic $topic): JsonResponse
    {
        $this->assertPublicTopic($topic);
        abort_unless($topic->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'body' => 'required|string|min:10|max:12000',
        ]);

        $topic->update(['body' => $data['body']]);

        return response()->json([
            'ok' => true,
            'data' => Forum::topicPayload($topic->fresh(['author', 'category', 'lastPostUser'])),
        ]);
    }

    public function updatePost(Request $request, ForumPost $post): JsonResponse
    {
        $this->assertPublicPost($post);
        abort_unless($post->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'body' => 'required|string|min:2|max:8000',
        ]);

        $post->update(['body' => $data['body']]);

        return response()->json([
            'ok' => true,
            'data' => Forum::postPayload($post->fresh(['votes', 'reactions']), $request->user()->id),
        ]);
    }

    public function votePost(Request $request, ForumPost $post): JsonResponse
    {
        $this->assertPublicPost($post);

        $data = $request->validate([
            'value' => 'required|integer|in:-1,1',
        ]);

        $vote = ForumPostVote::query()
            ->where('forum_post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($vote && $vote->value === (int) $data['value']) {
            $vote->delete();
        } else {
            ForumPostVote::updateOrCreate(
                ['forum_post_id' => $post->id, 'user_id' => $request->user()->id],
                ['value' => (int) $data['value']],
            );
        }

        $post->recalculateScore();
        $post->refresh();

        return response()->json([
            'ok' => true,
            'data' => Forum::postPayload($post->fresh(['votes', 'reactions']), $request->user()->id),
        ]);
    }

    public function toggleReaction(Request $request, ForumPost $post): JsonResponse
    {
        $this->assertPublicPost($post);

        $data = $request->validate([
            'emoji' => ['required', 'string', 'max:16', Rule::in(Forum::reactions())],
        ]);

        $reaction = ForumPostReaction::query()
            ->where('forum_post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->where('emoji', $data['emoji'])
            ->first();

        if ($reaction) {
            $reaction->delete();
        } else {
            ForumPostReaction::create([
                'forum_post_id' => $post->id,
                'user_id' => $request->user()->id,
                'emoji' => $data['emoji'],
            ]);
        }

        return response()->json([
            'ok' => true,
            'data' => Forum::postPayload($post->fresh(['votes', 'reactions']), $request->user()->id),
        ]);
    }

    public function user(string $slug): View
    {
        $user = Forum::userBySlug($slug);
        if (! $user) {
            abort(404);
        }

        $payload = Forum::userPayload($user);
        $levels = Forum::levels();
        $level = $levels[$payload['level']] ?? null;
        $orderedLevels = array_keys($levels);
        $currentIdx = array_search($payload['level'], $orderedLevels, true);
        $nextKey = $orderedLevels[$currentIdx + 1] ?? null;
        $nextLevel = $nextKey ? $levels[$nextKey] : null;
        $progress = $nextLevel && $level
            ? min(100, round(($payload['reputation'] - $level['min']) / max(1, ($nextLevel['min'] - $level['min'])) * 100))
            : 100;

        return view('pages.forum.user', [
            'user' => $payload,
            'level' => $level,
            'nextLevel' => $nextLevel,
            'progress' => $progress,
            'levels' => $levels,
            'topics' => Forum::topicsForUser($user),
            'replies' => Forum::postsForUser($user),
            'categories' => Forum::categories(),
            'allUsers' => Forum::users(),
        ]);
    }

    private function uniqueTopicSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'tema';
        $slug = $base;
        $i = 2;

        while (ForumTopic::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function assertPublicTopic(ForumTopic $topic): void
    {
        abort_unless(
            $topic->status === 'approved'
            && $topic->published_at
            && $topic->published_at->lte(now()),
            404,
        );
    }

    private function assertPublicPost(ForumPost $post): void
    {
        abort_unless(
            $post->status === 'approved'
            && $post->published_at
            && $post->published_at->lte(now()),
            404,
        );
    }
}
