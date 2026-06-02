<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Forum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function profile(Request $request): View
    {
        return view('pages.account.profile', [
            'user' => $request->user()->load('socialAccounts'),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'phone' => 'nullable|string|max:64',
            'forum_signature' => 'nullable|string|max:220',
            'marketing_consent' => 'sometimes|boolean',
        ]);

        $request->user()->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'forum_signature' => $data['forum_signature'] ?? null,
            'marketing_consent' => (bool) ($data['marketing_consent'] ?? false),
        ]);

        return back()->with('status', __('site.account.profile.saved'));
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,jpg,png,webp|max:3072',
        ]);

        $user = $request->user();
        $path = $request->file('avatar')->store('avatars', 'public');

        // Remove the previous locally-stored avatar (skip remote social URLs).
        if ($user->avatar_path && ! str_starts_with($user->avatar_path, 'http')) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->update(['avatar_path' => $path]);

        return response()->json([
            'ok' => true,
            'avatar_url' => $user->fresh()->avatar_url,
        ]);
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar_path && ! str_starts_with($user->avatar_path, 'http')) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->update(['avatar_path' => null]);

        return response()->json(['ok' => true]);
    }

    public function security(Request $request): View
    {
        return view('pages.account.security', [
            'user' => $request->user(),
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'password' => ['required', 'confirmed', Password::min(8)],
        ];

        // Social-only users have no password yet — they "set" one without
        // confirming a current password.
        if ($user->hasPassword()) {
            $rules['current_password'] = ['required', 'current_password:web'];
        }

        $request->validate($rules);

        $user->update(['password' => $request->input('password')]);

        return back()->with('status', __('site.account.security.password_saved'));
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        if ($data['email'] === $user->email) {
            return back()->with('status', __('site.account.security.email_same'));
        }

        $user->forceFill([
            'email' => $data['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();

        return back()->with('status', __('site.account.security.email_changed'));
    }

    public function addresses(Request $request): View
    {
        $addresses = $request->user()
            ->addresses()
            ->get()
            ->map(fn ($a) => AddressController::present($a));

        return view('pages.account.addresses', [
            'addresses' => $addresses,
        ]);
    }

    public function orders(Request $request): View
    {
        $user = $request->user();

        $orders = Order::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('email', $user->email);
            })
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return view('pages.account.orders', ['orders' => $orders]);
    }

    public function orderDetail(Request $request, string $order): JsonResponse
    {
        $user = $request->user();

        $model = Order::query()
            ->where('public_id', $order)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('email', $user->email);
            })
            ->with(['items', 'history'])
            ->firstOrFail();

        return response()->json(['data' => $model]);
    }

    public function reviews(Request $request): View
    {
        $reviews = $request->user()
            ->reviews()
            ->with(['product', 'images'])
            ->latest()
            ->paginate(10);

        return view('pages.account.reviews', ['reviews' => $reviews]);
    }

    public function destroyReview(Request $request, \App\Models\ProductReview $review): \Illuminate\Http\JsonResponse
    {
        abort_unless($review->user_id === $request->user()->id, 403);

        // Drop attached photos from disk, then the row (images cascade by FK).
        foreach ($review->images as $img) {
            if ($img->path && str_starts_with($img->path, '/storage/')) {
                Storage::disk('public')->delete(substr($img->path, strlen('/storage/')));
            }
        }

        $review->delete();

        return response()->json(['ok' => true]);
    }

    public function forumTopics(Request $request): View
    {
        $user = Forum::ensureUserProfile($request->user());

        $topics = $user->forumTopics()
            ->with('category')
            ->withCount(['approvedPosts'])
            ->paginate(10);

        return view('pages.account.forum-topics', [
            'topics' => $topics,
            'forumUser' => Forum::userPayload($user),
            'levels' => Forum::levels(),
        ]);
    }

    public function forumPosts(Request $request): View
    {
        $user = Forum::ensureUserProfile($request->user());

        $posts = $user->forumPosts()
            ->with(['topic.category'])
            ->paginate(10);

        return view('pages.account.forum-posts', [
            'posts' => $posts,
            'forumUser' => Forum::userPayload($user),
            'levels' => Forum::levels(),
        ]);
    }

    public function updateTopic(Request $request, \App\Models\ForumTopic $topic): \Illuminate\Http\JsonResponse
    {
        abort_unless($topic->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'title' => 'required|string|min:4|max:160',
            'body' => 'required|string|min:10|max:12000',
        ]);

        $topic->update($data);

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $topic->id,
                'title' => $topic->title,
                'body' => $topic->body,
                'slug' => $topic->slug,
            ],
        ]);
    }

    public function destroyTopic(Request $request, \App\Models\ForumTopic $topic): \Illuminate\Http\JsonResponse
    {
        abort_unless($topic->user_id === $request->user()->id, 403);

        $topic->delete();

        return response()->json(['ok' => true]);
    }

    public function updatePost(Request $request, \App\Models\ForumPost $post): \Illuminate\Http\JsonResponse
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'body' => 'required|string|min:2|max:8000',
        ]);

        $post->update(['body' => $data['body']]);

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $post->id,
                'body' => $post->body,
            ],
        ]);
    }

    public function destroyPost(Request $request, \App\Models\ForumPost $post): \Illuminate\Http\JsonResponse
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        $post->delete();

        return response()->json(['ok' => true]);
    }
}
