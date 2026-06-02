<?php

namespace Database\Seeders;

use App\Models\LabBatch;
use App\Models\LabBatchFile;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Переносит данные из config/lab-batches.php в БД (lab_batches + lab_batch_files
 * + связи с продуктами). Идемпотентен — обновляет существующие записи по lot.
 *
 * Связь lab_batch ↔ product подбирается best-effort по strain_slug/name
 * (через slug соответствующего strain-таксона) или по совпадению с
 * 'product_name' из конфига. Если ничего не сматчилось — батч без связки,
 * админ привяжет вручную из UI.
 */
class LabBatchesSeeder extends Seeder
{
    public function run(): void
    {
        $batches = config('lab-batches', []);
        if (empty($batches)) {
            return;
        }

        foreach ($batches as $lot => $data) {
            $batch = LabBatch::updateOrCreate(
                ['lot' => $data['lot'] ?? $lot],
                [
                    'product_name' => $data['product_name'] ?? null,
                    'strains' => $data['strains'] ?? [],
                    'package' => $data['package'] ?? null,
                    'mass' => $data['mass'] ?? null,
                    'lab_name' => 'VŠCHT Praha',
                    'received_at' => $data['received_at'] ?? null,
                    'issued_at' => $data['issued_at'] ?? null,
                    'tests' => $data['tests'] ?? [],
                    'published_at' => $data['issued_at'] ?? now(),
                ]
            );
            $batch->summary = $batch->recomputeSummary();
            $batch->save();

            // PDF протоколы — сохраняем как ссылки на существующие файлы в /public.
            // Файлы лежат как public/lab-tests.pdf и т.п. — disk 'public_root'
            // не существует, поэтому пишем как абсолютный путь '/lab-tests.pdf'
            // (MediaStorage::url отдаёт его как есть для путей с '/').
            $batch->files()->delete();
            foreach (($data['protocols'] ?? []) as $i => $proto) {
                LabBatchFile::create([
                    'lab_batch_id' => $batch->id,
                    'disk' => 'public',
                    'path' => '/' . ltrim($proto['file'], '/'),
                    'url' => '/' . ltrim($proto['file'], '/'),
                    'original_name' => $proto['file'],
                    'file_no' => $proto['no'] ?? null,
                    'label' => $proto['label'] ?? null,
                    'tested_at' => $proto['date'] ?? null,
                    'position' => $i + 1,
                ]);
            }

            // Привязка к продуктам по совпадению имени.
            $productIds = $this->resolveProductIds($data);
            if (! empty($productIds)) {
                $batch->products()->sync($productIds);
            }
        }
    }

    /** @return int[] */
    private function resolveProductIds(array $data): array
    {
        $names = array_filter(array_merge(
            [$data['product_name'] ?? null],
            $data['strains'] ?? []
        ));
        if (empty($names)) {
            return [];
        }

        $ids = [];
        foreach ($names as $name) {
            $needle = mb_strtolower(trim($name));

            // Ищем по JSON-имени (cs / en / ru / uk) и по slug
            $products = Product::query()
                ->where(function ($q) use ($needle) {
                    $q->where('slug', 'like', '%' . str_replace(' ', '-', $needle) . '%')
                      ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.cs"))) LIKE ?', ["%{$needle}%"])
                      ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) LIKE ?', ["%{$needle}%"]);
                })
                ->pluck('id')
                ->all();

            $ids = array_merge($ids, $products);
        }

        return array_values(array_unique($ids));
    }
}
