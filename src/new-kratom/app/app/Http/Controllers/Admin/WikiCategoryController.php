<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WikiCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WikiCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = WikiCategory::query()
            ->withCount('articles')
            ->orderBy('position')
            ->get();

        return response()->json(['data' => $categories]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $category = WikiCategory::create($data);

        return response()->json(['data' => $category], 201);
    }

    public function show(WikiCategory $category): JsonResponse
    {
        return response()->json(['data' => $category->loadCount('articles')]);
    }

    public function update(Request $request, WikiCategory $category): JsonResponse
    {
        $category->update($this->validated($request, $category));

        return response()->json(['data' => $category->fresh()]);
    }

    public function destroy(WikiCategory $category): JsonResponse
    {
        if ($category->articles()->exists()) {
            return response()->json([
                'message' => 'Nelze smazat kategorii s články. Nejprve přesuňte články jinam.',
            ], 422);
        }

        $category->delete();

        return response()->json(['ok' => true]);
    }

    private function validated(Request $request, ?WikiCategory $existing = null): array
    {
        return $request->validate([
            'slug' => [
                'required', 'string', 'max:80', 'regex:/^[a-z0-9\-]+$/',
                Rule::unique('wiki_categories', 'slug')->ignore($existing?->id),
            ],
            'title' => 'required|string|max:160',
            'eyebrow' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:32',
            'accent' => 'nullable|in:grass,terra,amber,cream,moss',
            'position' => 'nullable|integer|min:0|max:65535',
            'is_active' => 'boolean',
        ]);
    }
}
