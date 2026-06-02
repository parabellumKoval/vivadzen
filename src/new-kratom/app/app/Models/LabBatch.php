<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabBatch extends Model
{
    protected $fillable = [
        'lot', 'product_name', 'strains', 'package', 'mass',
        'lab_name', 'received_at', 'issued_at',
        'tests', 'summary', 'published_at',
    ];

    protected $casts = [
        'strains' => 'array',
        'tests' => 'array',
        'summary' => 'array',
        'received_at' => 'date',
        'issued_at' => 'date',
        'published_at' => 'datetime',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(LabBatchFile::class)->orderBy('position');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'lab_batch_product');
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * Пересчитать кэш summary (total/passed/ratio + headline-показатели).
     */
    public function recomputeSummary(): array
    {
        $tests = $this->tests ?? [];
        $total = 0;
        $passed = 0;

        foreach ($tests as $group) {
            foreach ((array) $group as $row) {
                $total++;
                if (($row['status'] ?? '') === 'V' || ($row['status'] ?? '') === 'Vn') {
                    $passed++;
                }
            }
        }

        $active = collect($tests['active'] ?? []);
        $mitragynin = $active->firstWhere('name', 'Mitragynin');
        $sevenOH = $active->firstWhere('name', '7-hydroxymitragynin');

        return [
            'total' => $total,
            'passed' => $passed,
            'ratio' => $total > 0 ? round($passed / $total, 4) : 0,
            'mitragynin' => $mitragynin ? ($mitragynin['value'] ?? null) : null,
            'h7mg' => $sevenOH ? ($sevenOH['value'] ?? null) : null,
        ];
    }
}
