<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ForumCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => ForumCategory::query()
                ->withCount('topics')
                ->orderBy('position')
                ->orderBy('label')
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateCategory($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['label']);

        $category = ForumCategory::create($data);

        return response()->json(['data' => $category], 201);
    }

    public function update(Request $request, ForumCategory $category): JsonResponse
    {
        $data = $this->validateCategory($request, $category);
        $data['slug'] = $data['slug'] ?: Str::slug($data['label']);

        $category->update($data);

        return response()->json(['data' => $category->fresh()->loadCount('topics')]);
    }

    public function destroy(ForumCategory $category): JsonResponse
    {
        $category->delete();

        return response()->json(['ok' => true]);
    }

    private function validateCategory(Request $request, ?ForumCategory $category = null): array
    {
        return $request->validate([
            'label' => 'required|string|max:120',
            'slug' => [
                'nullable',
                'string',
                'max:64',
                'regex:/^[a-z0-9\-]+$/',
                Rule::unique('forum_categories', 'slug')->ignore($category?->id),
            ],
            'icon' => 'required|string|max:16',
            'description' => 'nullable|string|max:2000',
            'position' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);
    }
}
