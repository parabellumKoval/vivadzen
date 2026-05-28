<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Админ-CRUD для Q&A.
 * Менеджер может писать и вопрос, и ответ от лица команды Vivadzen
 * (например, FAQ-style контент для seed-приёмов).
 */
class QuestionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductQuestion::query()->with('product:id,slug,name');

        if ($status = $request->query('status')) {
            if ($status === 'scheduled') {
                $query->where('status', 'approved')->where('published_at', '>', now());
            } elseif ($status === 'unanswered') {
                $query->whereNull('answer');
            } else {
                $query->where('status', $status);
            }
        }

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('author_name', 'like', "%{$search}%")
                  ->orWhere('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        if ($productId = $request->integer('product_id')) {
            $query->where('product_id', $productId);
        }

        $questions = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 25));
        $questions->getCollection()->transform(fn (ProductQuestion $q) => $this->shape($q));

        return response()->json(['data' => $questions]);
    }

    public function show(ProductQuestion $question): JsonResponse
    {
        return response()->json(['data' => $this->shape($question->load('product:id,slug,name'))]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateQuestion($request);
        Product::findOrFail($data['product_id']);

        if (! empty($data['answer']) && empty($data['answered_at'])) {
            $data['answered_at'] = now();
        }

        $question = ProductQuestion::create($data + [
            'created_by_admin_id' => $request->user()?->id,
        ]);

        return response()->json(['data' => $this->shape($question->fresh('product:id,slug,name'))], 201);
    }

    public function update(Request $request, ProductQuestion $question): JsonResponse
    {
        $data = $this->validateQuestion($request, $question);

        if (! empty($data['answer']) && empty($data['answered_at']) && ! $question->answered_at) {
            $data['answered_at'] = now();
        }

        $question->update($data);
        return response()->json(['data' => $this->shape($question->fresh('product:id,slug,name'))]);
    }

    public function destroy(ProductQuestion $question): JsonResponse
    {
        $question->delete();
        return response()->json(['ok' => true]);
    }

    public function approve(Request $request, ProductQuestion $question): JsonResponse
    {
        $publishedAt = $request->input('published_at')
            ? \Illuminate\Support\Carbon::parse($request->input('published_at'))
            : ($question->published_at ?? now());

        $question->update([
            'status'       => 'approved',
            'published_at' => $publishedAt,
        ]);
        return response()->json(['data' => $this->shape($question->fresh('product:id,slug,name'))]);
    }

    public function reject(ProductQuestion $question): JsonResponse
    {
        $question->update(['status' => 'rejected', 'published_at' => null]);
        return response()->json(['data' => $this->shape($question->fresh('product:id,slug,name'))]);
    }

    private function validateQuestion(Request $request, ?ProductQuestion $existing = null): array
    {
        $rules = [
            'product_id'   => ($existing ? 'sometimes|' : '') . 'required|integer|exists:products,id',
            'author_name'  => ($existing ? 'sometimes|' : '') . 'required|string|max:120',
            'author_email' => 'nullable|email|max:190',
            'question'     => ($existing ? 'sometimes|' : '') . 'required|string|min:5|max:2000',
            'answer'       => 'nullable|string|max:4000',
            'answered_by'  => 'nullable|string|max:120',
            'answered_at'  => 'nullable|date',
            'helpful_count'=> 'nullable|integer|min:0',
            'status'       => 'nullable|in:pending,approved,rejected',
            'published_at' => 'nullable|date',
        ];

        return $request->validate($rules);
    }

    private function shape(ProductQuestion $q): array
    {
        $payload = $q->toArray();
        $payload['product'] = $q->product ? [
            'id'   => $q->product->id,
            'slug' => $q->product->slug,
            'name' => is_array($q->product->name) ? ($q->product->name['cs'] ?? null) : $q->product->name,
        ] : null;
        return $payload;
    }
}
