<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->withCount(['orders', 'reviews', 'addresses', 'forumTopics', 'forumPosts']);

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->query('status') === 'blocked') {
            $query->whereNotNull('blocked_at');
        } elseif ($request->query('status') === 'active') {
            $query->whereNull('blocked_at');
        }

        return response()->json([
            'data' => $query->latest()->paginate($request->integer('per_page', 25)),
        ]);
    }

    public function show(User $user): JsonResponse
    {
        $user->loadCount(['orders', 'reviews', 'addresses']);
        $user->loadCount(['forumTopics', 'forumPosts']);
        $user->load(['addresses', 'socialAccounts']);

        // Orders linked by id OR by matching e-mail (covers guest checkouts).
        $orders = Order::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('email', $user->email);
            })
            ->withCount('items')
            ->latest()
            ->limit(50)
            ->get();

        $reviews = $user->reviews()->with('product:id,slug')->limit(50)->get();

        return response()->json([
            'data' => $user,
            'orders' => $orders,
            'reviews' => $reviews,
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:64',
            'marketing_consent' => 'sometimes|boolean',
            'forum_signature' => 'nullable|string|max:220',
            'forum_reputation' => 'nullable|integer|min:0',
        ]);

        $user->update($data);

        return response()->json(['data' => $user->fresh()]);
    }

    public function block(User $user): JsonResponse
    {
        $user->forceFill(['blocked_at' => now()])->save();

        return response()->json(['data' => $user->fresh()]);
    }

    public function unblock(User $user): JsonResponse
    {
        $user->forceFill(['blocked_at' => null])->save();

        return response()->json(['data' => $user->fresh()]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(['ok' => true]);
    }
}
