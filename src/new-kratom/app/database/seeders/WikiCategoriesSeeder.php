<?php

namespace Database\Seeders;

use App\Models\WikiCategory;
use Illuminate\Database\Seeder;

class WikiCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'slug' => 'botanika-a-veda',
                'title' => 'Botanika a věda',
                'eyebrow' => 'Rostlina a chemie',
                'description' => 'Mitragyna speciosa jako rostlina, její alkaloidní profil, fermentace a vědecký pohled na chemii kratomu.',
                'icon' => 'leaf',
                'accent' => 'grass',
                'position' => 10,
            ],
            [
                'slug' => 'historie-a-kultura',
                'title' => 'Historie a kultura',
                'eyebrow' => 'Etnobotanika',
                'description' => 'Tradiční použití kratomu v jihovýchodní Asii, historický kontext a regionální rozdíly.',
                'icon' => 'book-open',
                'accent' => 'amber',
                'position' => 20,
            ],
            [
                'slug' => 'legislativa-cr',
                'title' => 'Legislativa ČR',
                'eyebrow' => 'Právní rámec 2026',
                'description' => 'Aktuální regulace kratomu v České republice po novele 167/1998 Sb., role státních orgánů a srovnání s EU.',
                'icon' => 'gavel',
                'accent' => 'terra',
                'position' => 30,
            ],
            [
                'slug' => 'kvalita-a-bezpecnost',
                'title' => 'Kvalita a bezpečnost',
                'eyebrow' => 'Laboratoř a normy',
                'description' => 'Jak číst Certificate of Analysis, limity těžkých kovů a mykotoxinů, metody HPLC a ICP-MS, skladování.',
                'icon' => 'flask-conical',
                'accent' => 'cream',
                'position' => 40,
            ],
        ];

        foreach ($rows as $row) {
            WikiCategory::updateOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
