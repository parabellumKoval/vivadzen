<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class NpSettlement extends Model
{
    use Searchable;

    protected $table = 'np_settlements';
    protected $guarded = [];

    protected $casts = [
        'popular_rank' => 'integer',
    ];

    public function scoutShouldBeSearchable(): bool
    {
        return !empty($this->name_uk) || !empty($this->name_ru);
    }

    public function searchableAs(): string
    {
        return config('novaposhta.indexes.settlements', 'np_settlements');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'ref' => $this->ref,
            'name_uk' => $this->name_uk,
            'name_ru' => $this->name_ru,
            'area_uk' => $this->area_uk,
            'area_ru' => $this->area_ru,
            'region_uk' => $this->region_uk,
            'region_ru' => $this->region_ru,
            'type_uk' => $this->type_uk,
            'type_ru' => $this->type_ru,
            'popular_rank' => $this->popular_rank,
        ];
    }
}
