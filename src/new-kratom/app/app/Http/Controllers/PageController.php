<?php

namespace App\Http\Controllers;

use App\Models\LabBatch;

class PageController extends Controller
{
    public function delivery()     { return view('pages.static.delivery'); }
    public function lab()          { return view('pages.static.lab'); }
    public function licence()      { return view('pages.static.licence'); }
    public function stores()       { return view('pages.static.stores'); }
    public function returns()      { return view('pages.static.returns'); }
    public function contact()      { return view('pages.static.contact'); }
    public function support()      { return view('pages.static.support'); }
    public function about()        { return view('pages.static.about'); }
    public function terms()        { return view('pages.static.terms'); }
    public function privacy()      { return view('pages.static.privacy'); }
    public function cookies()      { return view('pages.static.cookies'); }
    public function subscription() { return view('pages.static.subscription'); }
    public function ageVerification() { return view('pages.static.age-verification'); }

    public function labBatch(string $batch)
    {
        $batch = strtoupper($batch);

        // 1) Сначала ищем в БД (актуальный источник)
        $model = LabBatch::query()
            ->with('files')
            ->where('lot', $batch)
            ->first();

        if ($model) {
            return view('pages.static.lab-batch', [
                'batchId' => $batch,
                'batch'   => $this->labBatchToArray($model),
            ]);
        }

        // 2) Фоллбек на статический конфиг — для обратной совместимости,
        //    пока не все партии перенесены в БД.
        $data = config("lab-batches.$batch");
        if (!$data) {
            abort(404);
        }

        return view('pages.static.lab-batch', [
            'batchId' => $batch,
            'batch'   => $data,
        ]);
    }

    /**
     * Приводим LabBatch к структуре, которую ожидает шаблон lab-batch.blade.php
     * (исторически он работал с массивом из config/lab-batches.php).
     */
    private function labBatchToArray(LabBatch $batch): array
    {
        return [
            'product_name' => $batch->product_name,
            'strains'      => $batch->strains ?? [],
            'lot'          => $batch->lot,
            'package'      => $batch->package,
            'mass'         => $batch->mass,
            'received_at'  => optional($batch->received_at)->format('Y-m-d'),
            'issued_at'    => optional($batch->issued_at)->format('Y-m-d'),
            'protocols'    => $batch->files->map(fn ($f) => [
                // asset() в шаблоне дополняет APP_URL — нужны относительные пути.
                // Абсолютные (bunny CDN, http://…) asset() пропускает as-is.
                'file'  => str_contains($f->public_url, '://')
                    ? $f->public_url
                    : ltrim($f->public_url, '/'),
                'no'    => $f->file_no,
                'label' => $f->label,
                'date'  => optional($f->tested_at)->format('Y-m-d'),
            ])->all(),
            'tests' => $batch->tests ?? [],
        ];
    }
}
