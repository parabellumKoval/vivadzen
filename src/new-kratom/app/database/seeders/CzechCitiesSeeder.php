<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Imports every Czech populated place from the GeoNames CZ dump.
 *
 * Source files (CC-BY 4.0):
 *  - https://download.geonames.org/export/dump/CZ.zip
 *  - https://download.geonames.org/export/dump/admin1CodesASCII.txt
 *  - https://download.geonames.org/export/dump/admin2Codes.txt
 *
 * GeoNames feature classes: we keep "P" (populated places, ~25k rows for CZ).
 */
class CzechCitiesSeeder extends Seeder
{
    private const COUNTRY_DUMP_URL  = 'https://download.geonames.org/export/dump/CZ.zip';
    private const ADMIN1_URL        = 'https://download.geonames.org/export/dump/admin1CodesASCII.txt';
    private const ADMIN2_URL        = 'https://download.geonames.org/export/dump/admin2Codes.txt';
    private const CACHE_DIR         = 'seeds/geonames';

    /**
     * GeoNames ships the well-known place and region names in English
     * (Prague, Pilsen, Pilsen Region…). For a Czech audience we localise them.
     */
    private const REGION_NAME_CS = [
        '52' => 'Hlavní město Praha',
        '78' => 'Jihomoravský kraj',
        '79' => 'Jihočeský kraj',
        '80' => 'Kraj Vysočina',
        '81' => 'Karlovarský kraj',
        '82' => 'Královéhradecký kraj',
        '83' => 'Liberecký kraj',
        '84' => 'Olomoucký kraj',
        '85' => 'Moravskoslezský kraj',
        '86' => 'Pardubický kraj',
        '87' => 'Plzeňský kraj',
        '88' => 'Středočeský kraj',
        '89' => 'Ústecký kraj',
        '90' => 'Zlínský kraj',
    ];

    /** GeoNames id → Czech canonical name (English aliases → Czech). */
    private const CITY_NAME_CS = [
        3067696 => 'Praha',  // Prague
        3068160 => 'Plzeň',  // Pilsen
    ];

    public function run(): void
    {
        Storage::disk('local')->makeDirectory(self::CACHE_DIR);

        $admin1 = $this->loadAdmin1();
        $admin2 = $this->loadAdmin2();
        $cz     = $this->downloadCountryDump();

        $this->command?->info(sprintf('Importing GeoNames CZ from %s', $cz));

        $handle = fopen($cz, 'rb');
        if (! $handle) {
            throw new \RuntimeException('Cannot open GeoNames CZ.txt');
        }

        // Wipe before reseeding so re-running is idempotent.
        DB::table('cities')->delete();

        $batch = [];
        $count = 0;
        $now   = now();

        while (($line = fgets($handle)) !== false) {
            $cols = explode("\t", rtrim($line, "\r\n"));
            if (count($cols) < 19) {
                continue;
            }

            // GeoNames column order — see https://download.geonames.org/export/dump/readme.txt
            [
                $geonameId, $name, $asciiName, $altNames,
                $latitude, $longitude, $featureClass, $featureCode,
                $countryCode, $cc2,
                $admin1Code, $admin2Code, $admin3Code, $admin4Code,
                $population, $elevation, $dem, $timezone, $modDate,
            ] = $cols;

            if ($featureClass !== 'P' || $countryCode !== 'CZ') {
                continue;
            }
            // Skip historic/abolished places.
            if (in_array($featureCode, ['PPLH', 'PPLQ', 'PPLW'], true)) {
                continue;
            }

            $regionKey   = 'CZ.' . $admin1Code;
            $districtKey = 'CZ.' . $admin1Code . '.' . $admin2Code;
            $regionName  = self::REGION_NAME_CS[$admin1Code] ?? ($admin1[$regionKey] ?? null);
            $cityName    = self::CITY_NAME_CS[(int) $geonameId] ?? $name;

            $batch[] = [
                'geonames_id'   => (int) $geonameId,
                'name'          => $cityName,
                'ascii_name'    => $asciiName ?: $cityName,
                'region_name'   => $regionName,
                'region_code'   => $admin1Code ?: null,
                'district_name' => $admin2[$districtKey] ?? null,
                'district_code' => $admin2Code ?: null,
                'feature_code'  => $featureCode ?: null,
                'latitude'      => $latitude !== '' ? (float) $latitude : null,
                'longitude'     => $longitude !== '' ? (float) $longitude : null,
                'population'    => (int) $population,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];

            if (count($batch) >= 1000) {
                DB::table('cities')->insert($batch);
                $count += count($batch);
                $batch = [];
            }
        }

        if ($batch) {
            DB::table('cities')->insert($batch);
            $count += count($batch);
        }

        fclose($handle);

        $this->command?->info(sprintf('Imported %d Czech cities.', $count));

        // Push to Meilisearch only if Scout is configured for it.
        if (config('scout.driver') === 'meilisearch') {
            $this->command?->info('Indexing cities in Meilisearch…');
            try {
                /** @var \Meilisearch\Client $client */
                $client = app(\Meilisearch\Client::class);
                $client->index((new City())->searchableAs())->deleteAllDocuments();
            } catch (\Throwable $e) {
                // index may not exist yet — Scout will create it.
            }
            City::makeAllSearchable();
            $this->configureMeilisearchIndex();
        }
    }

    /** @return array<string, string> */
    private function loadAdmin1(): array
    {
        $path = $this->downloadTo('admin1CodesASCII.txt', self::ADMIN1_URL);
        $out = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            if (! str_starts_with($line, 'CZ.')) {
                continue;
            }
            $cols = explode("\t", $line);
            $out[$cols[0]] = $cols[1] ?? null;
        }
        return $out;
    }

    /** @return array<string, string> */
    private function loadAdmin2(): array
    {
        $path = $this->downloadTo('admin2Codes.txt', self::ADMIN2_URL);
        $out = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            if (! str_starts_with($line, 'CZ.')) {
                continue;
            }
            $cols = explode("\t", $line);
            $out[$cols[0]] = $cols[1] ?? null;
        }
        return $out;
    }

    private function downloadTo(string $name, string $url): string
    {
        $disk = Storage::disk('local');
        $relative = self::CACHE_DIR . '/' . $name;
        if (! $disk->exists($relative)) {
            $this->command?->info("Downloading {$url}…");
            $body = Http::timeout(120)->get($url)->throw()->body();
            $disk->put($relative, $body);
        }
        return $disk->path($relative);
    }

    private function downloadCountryDump(): string
    {
        $disk = Storage::disk('local');
        $zipRelative = self::CACHE_DIR . '/CZ.zip';
        $txtRelative = self::CACHE_DIR . '/CZ.txt';

        if (! $disk->exists($txtRelative)) {
            if (! $disk->exists($zipRelative)) {
                $this->command?->info('Downloading GeoNames CZ.zip (~5 MB)…');
                $body = Http::timeout(300)->get(self::COUNTRY_DUMP_URL)->throw()->body();
                $disk->put($zipRelative, $body);
            }
            $zip = new \ZipArchive();
            if ($zip->open($disk->path($zipRelative)) !== true) {
                throw new \RuntimeException('Cannot open CZ.zip');
            }
            $zip->extractTo($disk->path(self::CACHE_DIR));
            $zip->close();
        }

        return $disk->path($txtRelative);
    }

    /**
     * Tune the Meilisearch index for typo-tolerant autocomplete:
     *  - search "Brno" or "Brn" or "Brrno" — all should land on Brno
     *  - rank by population so capitals/regional centres come first
     */
    private function configureMeilisearchIndex(): void
    {
        try {
            /** @var \Meilisearch\Client $client */
            $client = app(\Meilisearch\Client::class);
            $index  = $client->index((new City())->searchableAs());

            $index->updateSearchableAttributes(['name', 'ascii_name', 'district_name', 'region_name']);
            $index->updateFilterableAttributes(['region_name', 'district_name']);
            $index->updateSortableAttributes(['population']);
            $index->updateRankingRules([
                'words',
                'typo',
                'proximity',
                'attribute',
                'sort',
                'exactness',
                'population:desc',
            ]);
            $index->updateTypoTolerance([
                'enabled' => true,
                'minWordSizeForTypos' => ['oneTypo' => 3, 'twoTypos' => 6],
            ]);
        } catch (\Throwable $e) {
            $this->command?->warn('Could not configure Meilisearch index: ' . $e->getMessage());
        }
    }
}
