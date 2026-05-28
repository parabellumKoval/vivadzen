<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            ['slug' => 'zeleny',  'label' => 'Zelený',  'meta' => ['h1' => 'Zelený kratom', 'vein' => 'green',  'accent' => 'grass',      'rangeMin' => '1,2', 'rangeMax' => '1,6', 'dose' => '3–5 g']],
            ['slug' => 'bily',    'label' => 'Bílý',    'meta' => ['h1' => 'Bílý kratom',   'vein' => 'white',  'accent' => 'cream',      'rangeMin' => '1,4', 'rangeMax' => '1,7', 'dose' => '3–4 g']],
            ['slug' => 'cerveny', 'label' => 'Červený', 'meta' => ['h1' => 'Červený kratom','vein' => 'red',    'accent' => 'terracotta', 'rangeMin' => '1,3', 'rangeMax' => '1,6', 'dose' => '3–5 g']],
            ['slug' => 'zluty',   'label' => 'Žlutý',   'meta' => ['h1' => 'Žlutý kratom',  'vein' => 'yellow', 'accent' => 'amber',      'rangeMin' => '1,2', 'rangeMax' => '1,5', 'dose' => '3–5 g', 'comingSoon' => true]],
        ];
        foreach ($colors as $i => $c) {
            Taxonomy::updateOrCreate(
                ['type' => 'color', 'slug' => $c['slug']],
                [
                    'label' => ['cs' => $c['label'], 'en' => $c['label']],
                    'meta' => array_merge($c['meta'], [
                        'h1_i18n' => ['cs' => $c['meta']['h1']],
                        'dose_i18n' => ['cs' => $c['meta']['dose']],
                    ]),
                    'position' => $i,
                ]
            );
        }

        $strains = [
            ['slug' => 'maeng-da', 'label' => 'Maeng Da', 'origin' => 'Indonésie · klasická'],
            ['slug' => 'sumatra',  'label' => 'Sumatra',  'origin' => 'Indonésie · ostrov'],
            ['slug' => 'thajsky',  'label' => 'Thajský',  'origin' => 'Thajsko · tradiční'],
            ['slug' => 'slon',     'label' => 'Slon',     'origin' => 'Borneo · Elephant'],
            ['slug' => 'borneo',   'label' => 'Borneo',   'origin' => 'Borneo · region',  'comingSoon' => true],
            ['slug' => 'rurut',    'label' => 'Rurut',    'origin' => 'Borneo · speciální'],
        ];
        foreach ($strains as $i => $s) {
            $meta = ['origin' => $s['origin']];
            if (! empty($s['comingSoon'])) $meta['comingSoon'] = true;
            Taxonomy::updateOrCreate(
                ['type' => 'strain', 'slug' => $s['slug']],
                [
                    'label' => ['cs' => $s['label'], 'en' => $s['label']],
                    'meta' => array_merge($meta, [
                        'origin_i18n' => ['cs' => $s['origin']],
                    ]),
                    'position' => $i,
                ]
            );
        }

        $forms = [
            ['slug' => 'prasek',  'label' => 'Prášek',  'sub' => 'Klasická forma'],
            ['slug' => 'extrakt', 'label' => 'Extrakt', 'sub' => 'Koncentrát'],
            ['slug' => 'nano',    'label' => 'Nano',    'sub' => 'Jemně mletý'],
        ];
        foreach ($forms as $i => $f) {
            Taxonomy::updateOrCreate(
                ['type' => 'form', 'slug' => $f['slug']],
                [
                    'label' => ['cs' => $f['label']],
                    'meta' => [
                        'sub' => $f['sub'],
                        'sub_i18n' => ['cs' => $f['sub']],
                    ],
                    'position' => $i,
                ]
            );
        }
    }
}
