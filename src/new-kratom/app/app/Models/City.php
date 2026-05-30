<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class City extends Model
{
    use Searchable;

    protected $fillable = [
        'geonames_id', 'name', 'ascii_name',
        'region_name', 'region_code',
        'district_name', 'district_code',
        'feature_code', 'latitude', 'longitude', 'population',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'population' => 'integer',
    ];

    public function searchableAs(): string
    {
        return 'cities_cz';
    }

    /**
     * Document fed to Meilisearch. The duplicate "name" entries (e.g.
     * the four "Lhota" villages) are disambiguated by district_name, which
     * Meilisearch exposes for ranking and the UI shows in parentheses.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ascii_name' => $this->ascii_name,
            'district_name' => $this->district_name,
            'region_name' => $this->region_name,
            'population' => (int) $this->population,
        ];
    }

    /**
     * Population-weighted ranking: typing "Praha" should surface the
     * capital, not the village in Příbram district.
     */
    public function scoutMetadata(): array
    {
        return [];
    }

    /**
     * Human-readable label with disambiguation. Example:
     *  - "Praha"
     *  - "Lhota (Příbram)" when the simple name is ambiguous
     */
    public function getLabelAttribute(): string
    {
        return $this->name;
    }

    public function getFullLabelAttribute(): string
    {
        return $this->district_name
            ? sprintf('%s (%s)', $this->name, $this->district_name)
            : $this->name;
    }
}
