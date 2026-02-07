<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class NpWarehouse extends Model
{
    use Searchable;

    protected $table = 'np_warehouses';
    protected $guarded = [];

    protected $casts = [
        'is_postomat' => 'boolean',
    ];

    public function scoutShouldBeSearchable(): bool
    {
        return !empty($this->name_uk) || !empty($this->name_ru);
    }

    public function searchableAs(): string
    {
        return config('novaposhta.indexes.warehouses', 'np_warehouses');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'ref' => $this->ref,
            'settlement_ref' => $this->settlement_ref,
            'name_uk' => $this->name_uk,
            'name_ru' => $this->name_ru,
            'category' => $this->category,
            'type' => $this->type,
            'is_postomat' => $this->is_postomat,
        ];
    }
}
