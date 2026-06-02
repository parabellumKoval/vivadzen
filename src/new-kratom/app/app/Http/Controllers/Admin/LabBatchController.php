<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LabBatch;
use App\Models\LabBatchFile;
use App\Services\MediaStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Лабораторные тесты (COA / Šarže).
 *
 * Партия (lot) — самостоятельная сущность. Один lab_batch может быть
 * привязан к нескольким продуктам через pivot lab_batch_product.
 *
 * tests хранятся как JSON со структурой:
 *   { active: [...], metals: [...], mycotoxins: [...], pah: [...], microbiology: [...] }
 * Каждая запись:
 *   { name, symbol?, value, uncertainty?, below_loq?, unit, limit, status }
 *   status ∈ V (pass), Vn (pass within uncertainty), N (fail), X (info / not evaluated)
 */
class LabBatchController extends Controller
{
    public function __construct(private readonly MediaStorage $storage) {}

    public function index(Request $request): JsonResponse
    {
        $query = LabBatch::query()
            ->withCount('files')
            ->with(['products:id,slug,name']);

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('lot', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%");
            });
        }

        $batches = $query->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 25));

        $batches->getCollection()->transform(fn (LabBatch $b) => $this->shapeList($b));

        return response()->json(['data' => $batches]);
    }

    public function show(LabBatch $labBatch): JsonResponse
    {
        $labBatch->load(['files', 'products:id,slug,name']);
        return response()->json(['data' => $this->shapeFull($labBatch)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePayload($request);

        $batch = DB::transaction(function () use ($data) {
            /** @var LabBatch $batch */
            $batch = LabBatch::create($data['batch']);
            $batch->summary = $batch->recomputeSummary();
            $batch->save();

            if (! empty($data['product_ids'])) {
                $batch->products()->sync($data['product_ids']);
            }
            return $batch;
        });

        return response()->json(['data' => $this->shapeFull($batch->load(['files', 'products:id,slug,name']))], 201);
    }

    public function update(Request $request, LabBatch $labBatch): JsonResponse
    {
        $data = $this->validatePayload($request, $labBatch);

        DB::transaction(function () use ($data, $labBatch) {
            $labBatch->fill($data['batch']);
            $labBatch->summary = $labBatch->recomputeSummary();
            $labBatch->save();

            if (array_key_exists('product_ids', $data)) {
                $labBatch->products()->sync($data['product_ids']);
            }
        });

        return response()->json(['data' => $this->shapeFull($labBatch->fresh(['files', 'products:id,slug,name']))]);
    }

    public function destroy(LabBatch $labBatch): JsonResponse
    {
        // Удаляем сами файлы PDF на диске
        foreach ($labBatch->files as $file) {
            $this->storage->delete($file->disk, $file->path);
        }
        $labBatch->delete();

        return response()->json(['ok' => true]);
    }

    // ───────────────────────── PDF files ─────────────────────────

    public function storeFile(Request $request, LabBatch $labBatch): JsonResponse
    {
        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|mimes:pdf|max:20480', // 20 MB
            'file_no' => 'nullable|string|max:64',
            'label' => 'nullable|string|max:255',
            'tested_at' => 'nullable|date',
        ]);

        $startPos = (int) ($labBatch->files()->max('position') ?? 0) + 1;
        $created = [];

        foreach ($request->file('files') as $i => $file) {
            $stored = $this->storage->store($file, "lab-batches/{$labBatch->lot}");

            $row = $labBatch->files()->create([
                'disk' => $stored['disk'],
                'path' => $stored['path'],
                'url' => $stored['url'],
                'original_name' => $stored['filename'] ?? null,
                'size' => $stored['size'] ?? null,
                'file_no' => $request->input('file_no'),
                'label' => $request->input('label'),
                'tested_at' => $request->input('tested_at'),
                'position' => $startPos + $i,
            ]);

            $created[] = $this->shapeFile($row);
        }

        return response()->json(['data' => $created], 201);
    }

    public function updateFile(Request $request, LabBatch $labBatch, LabBatchFile $file): JsonResponse
    {
        abort_unless($file->lab_batch_id === $labBatch->id, 404);

        $data = $request->validate([
            'file_no' => 'nullable|string|max:64',
            'label' => 'nullable|string|max:255',
            'tested_at' => 'nullable|date',
            'position' => 'nullable|integer|min:0',
        ]);

        $file->update($data);

        return response()->json(['data' => $this->shapeFile($file->fresh())]);
    }

    public function destroyFile(LabBatch $labBatch, LabBatchFile $file): JsonResponse
    {
        abort_unless($file->lab_batch_id === $labBatch->id, 404);

        $this->storage->delete($file->disk, $file->path);
        $file->delete();

        return response()->json(['ok' => true]);
    }

    public function reorderFiles(Request $request, LabBatch $labBatch): JsonResponse
    {
        $data = $request->validate([
            'order' => 'required|array|min:1',
            'order.*' => 'integer|min:1',
        ]);

        $owned = $labBatch->files()->whereIn('id', $data['order'])->pluck('id')->all();

        DB::transaction(function () use ($data, $owned, $labBatch) {
            foreach ($data['order'] as $i => $id) {
                if (! in_array($id, $owned, true)) {
                    continue;
                }
                $labBatch->files()->where('id', $id)->update(['position' => $i + 1]);
            }
        });

        return response()->json([
            'data' => $labBatch->files()->orderBy('position')->get()
                ->map(fn (LabBatchFile $f) => $this->shapeFile($f)),
        ]);
    }

    // ───────────────────────── helpers ─────────────────────────

    private function validatePayload(Request $request, ?LabBatch $existing = null): array
    {
        $lotRule = 'required|string|max:64|unique:lab_batches,lot';
        if ($existing) {
            $lotRule .= ",{$existing->id}";
        }

        $statuses = 'in:V,Vn,N,X';
        $groups = ['active', 'metals', 'mycotoxins', 'pah', 'microbiology'];

        $rules = [
            'lot' => $lotRule,
            'product_name' => 'nullable|string|max:255',
            'strains' => 'nullable|array',
            'strains.*' => 'string|max:128',
            'package' => 'nullable|string|max:255',
            'mass' => 'nullable|string|max:32',
            'lab_name' => 'nullable|string|max:255',
            'received_at' => 'nullable|date',
            'issued_at' => 'nullable|date',
            'published_at' => 'nullable|date',
            'tests' => 'nullable|array',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ];

        foreach ($groups as $g) {
            $rules["tests.{$g}"] = 'nullable|array';
            $rules["tests.{$g}.*.name"] = 'required|string|max:255';
            $rules["tests.{$g}.*.symbol"] = 'nullable|string|max:8';
            $rules["tests.{$g}.*.value"] = 'required';
            $rules["tests.{$g}.*.uncertainty"] = 'nullable|numeric';
            $rules["tests.{$g}.*.below_loq"] = 'nullable|boolean';
            $rules["tests.{$g}.*.unit"] = 'nullable|string|max:32';
            $rules["tests.{$g}.*.limit"] = 'nullable';
            $rules["tests.{$g}.*.status"] = "nullable|string|{$statuses}";
        }

        $validated = $request->validate($rules);

        $productIds = $validated['product_ids'] ?? null;
        unset($validated['product_ids']);

        $payload = [
            'batch' => $validated,
        ];
        if ($productIds !== null) {
            $payload['product_ids'] = $productIds;
        }

        return $payload;
    }

    /** @return array<string, mixed> */
    private function shapeList(LabBatch $b): array
    {
        return [
            'id' => $b->id,
            'lot' => $b->lot,
            'product_name' => $b->product_name,
            'strains' => $b->strains,
            'mass' => $b->mass,
            'issued_at' => optional($b->issued_at)->format('Y-m-d'),
            'received_at' => optional($b->received_at)->format('Y-m-d'),
            'published_at' => optional($b->published_at)->format('Y-m-d'),
            'files_count' => (int) ($b->files_count ?? 0),
            'summary' => $b->summary,
            'products' => $b->products->map(fn ($p) => [
                'id' => $p->id,
                'slug' => $p->slug,
                'name' => $p->name,
            ])->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function shapeFull(LabBatch $b): array
    {
        return [
            'id' => $b->id,
            'lot' => $b->lot,
            'product_name' => $b->product_name,
            'strains' => $b->strains ?? [],
            'package' => $b->package,
            'mass' => $b->mass,
            'lab_name' => $b->lab_name,
            'received_at' => optional($b->received_at)->format('Y-m-d'),
            'issued_at' => optional($b->issued_at)->format('Y-m-d'),
            'published_at' => optional($b->published_at)->format('Y-m-d'),
            'tests' => $b->tests ?? [
                'active' => [], 'metals' => [], 'mycotoxins' => [], 'pah' => [], 'microbiology' => [],
            ],
            'summary' => $b->summary,
            'files' => $b->files->map(fn (LabBatchFile $f) => $this->shapeFile($f))->all(),
            'product_ids' => $b->products->pluck('id')->all(),
            'products' => $b->products->map(fn ($p) => [
                'id' => $p->id,
                'slug' => $p->slug,
                'name' => $p->name,
            ])->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function shapeFile(LabBatchFile $f): array
    {
        return [
            'id' => $f->id,
            'disk' => $f->disk,
            'path' => $f->path,
            'url' => $f->public_url,
            'original_name' => $f->original_name,
            'file_no' => $f->file_no,
            'label' => $f->label,
            'tested_at' => optional($f->tested_at)->format('Y-m-d'),
            'size' => $f->size,
            'position' => $f->position,
        ];
    }
}
