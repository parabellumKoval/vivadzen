<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

/**
 * Перенос ранее-static-каталога (App\Support\Catalog) в БД kratom.
 * После seed-а DatabaseSeeder вызывает CacheWarmer и Redis заполняется
 * автоматически.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = $this->staticCatalog();

        foreach ($catalog as $i => $p) {
            $product = Product::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'name' => ['cs' => $p['name']],
                    'short' => ['cs' => $p['short']],
                    'description' => ['cs' => $p['description']],
                    'color_slug' => $p['color'],
                    'strain_slug' => $p['strain'],
                    'form_slug' => $p['form'],
                    'origin' => $p['origin'],
                    'origin_i18n' => ['cs' => $p['origin']],
                    'mitragynin' => (float) str_replace(',', '.', (string) $p['mitragynin']),
                    'h7mg' => (float) str_replace(',', '.', (string) $p['h7mg']),
                    'purity' => (float) str_replace(',', '.', (string) $p['purity']),
                    'batch' => $p['batch'],
                    'tested_at' => $this->parseCsDate($p['testedAt']),
                    'grind' => $p['grind'],
                    'grind_i18n' => ['cs' => $p['grind']],
                    'rating' => $p['rating'],
                    'reviews_count' => $p['reviewsCount'],
                    'questions_count' => $p['questionsCount'],
                    'in_stock' => $p['inStock'],
                    'badge' => $p['badge'],
                    'main_image' => $p['image'],
                    'position' => $i,
                    'published_at' => now(),
                ]
            );

            // Варианты 25/50 г (или 10 мл для экстракта)
            $unit = $p['unit'] ?? 'g';
            $unitSize = $p['unitSize'] ?? null;

            if ($unit === 'ml' && $unitSize) {
                ProductVariant::updateOrCreate(
                    ['product_id' => $product->id, 'size' => $unitSize, 'unit' => 'ml'],
                    ['price' => $p['price25']]
                );
            } else {
                ProductVariant::updateOrCreate(
                    ['product_id' => $product->id, 'size' => 25, 'unit' => 'g'],
                    ['price' => $p['price25']]
                );
                if (! empty($p['price50'])) {
                    ProductVariant::updateOrCreate(
                        ['product_id' => $product->id, 'size' => 50, 'unit' => 'g'],
                        ['price' => $p['price50']]
                    );
                }
            }

            // Изображения галереи
            foreach ($p['gallery'] as $j => $img) {
                ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'path' => $img],
                    ['alt' => $p['name'], 'position' => $j]
                );
            }
        }
    }

    private function parseCsDate(string $cs): ?string
    {
        // "12. 03. 2026" → "2026-03-12"
        if (! preg_match('/^(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{4})$/', $cs, $m)) {
            return null;
        }
        return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
    }

    /**
     * Скопирован из старой App\Support\Catalog (до перевода на репозитории).
     * Поддерживается здесь как фиксированная фикстура для воспроизводимости.
     */
    private function staticCatalog(): array
    {
        return [
            [
                'slug' => 'cervena-maeng-da',
                'name' => 'Červená Maeng Da',
                'short' => 'Klasická červená odrůda · Borneo',
                'color' => 'cerveny', 'strain' => 'maeng-da', 'form' => 'prasek',
                'origin' => 'Borneo, Indonésie',
                'grind' => 'jemně mletý',
                'mitragynin' => '1,42', 'h7mg' => '0,008', 'purity' => '99,1',
                'batch' => 'VD-2026-014', 'testedAt' => '12. 03. 2026',
                'price25' => 290, 'price50' => 520,
                'rating' => 4.9, 'reviewsCount' => 124, 'questionsCount' => 8,
                'inStock' => true, 'badge' => ['variant' => 'sale', 'label' => '−10 %'],
                'image' => '/assets/products/cervena-maeng-da/01-front.png',
                'gallery' => ['/assets/products/cervena-maeng-da/01-front.png'],
                'description' => 'Klasická červená odrůda Maeng Da z indonéských plantáží na ostrově Borneo. List prochází tradičním sušením s krátkou kontrolovanou fermentací. Jemně mletý práškový profil je vhodný pro přípravu studených i teplých nálevů. Šarže VD-2026-014 byla testována v akreditované laboratoři VŠCHT Praha podle normy ISO 17025.',
            ],
            [
                'slug' => 'zelena-maeng-da',
                'name' => 'Zelená Maeng Da',
                'short' => 'Klasická zelená odrůda · Borneo',
                'color' => 'zeleny', 'strain' => 'maeng-da', 'form' => 'prasek',
                'origin' => 'Borneo, Indonésie', 'grind' => 'jemně mletý',
                'mitragynin' => '1,38', 'h7mg' => '0,007', 'purity' => '99,0',
                'batch' => 'VD-2026-011', 'testedAt' => '02. 03. 2026',
                'price25' => 280, 'price50' => 500,
                'rating' => 4.8, 'reviewsCount' => 96, 'questionsCount' => 5,
                'inStock' => true, 'badge' => null,
                'image' => '/assets/products/zelena-maeng-da/01-front.png',
                'gallery' => ['/assets/products/zelena-maeng-da/01-front.png', '/assets/products/zelena-maeng-da/04-macro.png'],
                'description' => 'Zelená Maeng Da představuje klasický profil odrůdy ze severního Bornea. Sušení probíhá v kontrolovaných podmínkách bez fermentace.',
            ],
            [
                'slug' => 'bila-maeng-da',
                'name' => 'Bílá Maeng Da', 'short' => 'Mladý list · Borneo',
                'color' => 'bily', 'strain' => 'maeng-da', 'form' => 'prasek',
                'origin' => 'Borneo, Indonésie', 'grind' => 'jemně mletý',
                'mitragynin' => '1,55', 'h7mg' => '0,009', 'purity' => '99,2',
                'batch' => 'VD-2026-009', 'testedAt' => '20. 02. 2026',
                'price25' => 310, 'price50' => 560,
                'rating' => 4.9, 'reviewsCount' => 186, 'questionsCount' => 12,
                'inStock' => true, 'badge' => ['variant' => 'subscription', 'label' => 'Předplatné'],
                'image' => '/assets/products/bila-maeng-da/01-front.png',
                'gallery' => ['/assets/products/bila-maeng-da/01-front.png'],
                'description' => 'Bílá Maeng Da pochází z mladších listů, sklízených dříve v cyklu.',
            ],
            [
                'slug' => 'zelena-sumatra',
                'name' => 'Zelená Sumatra', 'short' => 'Indonéská klasika · Sumatra',
                'color' => 'zeleny', 'strain' => 'sumatra', 'form' => 'prasek',
                'origin' => 'Sumatra, Indonésie', 'grind' => 'jemně mletý',
                'mitragynin' => '1,32', 'h7mg' => '0,006', 'purity' => '99,0',
                'batch' => 'VD-2026-012', 'testedAt' => '05. 03. 2026',
                'price25' => 270, 'price50' => 490,
                'rating' => 4.8, 'reviewsCount' => 98, 'questionsCount' => 4,
                'inStock' => true, 'badge' => null,
                'image' => '/assets/products/zelena-sumatra/01-front.png',
                'gallery' => ['/assets/products/zelena-sumatra/01-front.png'],
                'description' => 'Zelená Sumatra z divoce rostoucích stromů na severní Sumatře.',
            ],
            [
                'slug' => 'zeleny-thajsky',
                'name' => 'Zelený thajský', 'short' => 'Tradiční thajský list',
                'color' => 'zeleny', 'strain' => 'thajsky', 'form' => 'prasek',
                'origin' => 'Jižní Thajsko', 'grind' => 'jemně mletý',
                'mitragynin' => '1,28', 'h7mg' => '0,005', 'purity' => '99,1',
                'batch' => 'VD-2026-007', 'testedAt' => '15. 02. 2026',
                'price25' => 260, 'price50' => 470,
                'rating' => 4.7, 'reviewsCount' => 58, 'questionsCount' => 3,
                'inStock' => true, 'badge' => null,
                'image' => '/assets/products/zeleny-thajsky/01-front.png',
                'gallery' => ['/assets/products/zeleny-thajsky/01-front.png'],
                'description' => 'Zelený thajský kratom je tradiční odrůdou jižního Thajska.',
            ],
            [
                'slug' => 'zeleny-rurut-nano',
                'name' => 'Zelený Rurut Nano', 'short' => 'Jemně mletý nano profil',
                'color' => 'zeleny', 'strain' => 'rurut', 'form' => 'nano',
                'origin' => 'Borneo, Indonésie', 'grind' => 'nano (≤ 50 μm)',
                'mitragynin' => '1,46', 'h7mg' => '0,008', 'purity' => '99,3',
                'batch' => 'VD-2026-013', 'testedAt' => '08. 03. 2026',
                'price25' => 360, 'price50' => 640,
                'rating' => 4.8, 'reviewsCount' => 41, 'questionsCount' => 6,
                'inStock' => true, 'badge' => ['variant' => 'tag-amber', 'label' => 'NANO'],
                'image' => '/assets/products/zeleny-rurut-nano/01-front.png',
                'gallery' => ['/assets/products/zeleny-rurut-nano/01-front.png'],
                'description' => 'Rurut Nano je speciální odrůda zeleného kratomu.',
            ],
            [
                'slug' => 'bily-slon',
                'name' => 'Bílý slon', 'short' => 'Elephant variety · Borneo',
                'color' => 'bily', 'strain' => 'slon', 'form' => 'prasek',
                'origin' => 'Borneo, Indonésie', 'grind' => 'jemně mletý',
                'mitragynin' => '1,49', 'h7mg' => '0,007', 'purity' => '99,1',
                'batch' => 'VD-2026-006', 'testedAt' => '10. 02. 2026',
                'price25' => 320, 'price50' => 580,
                'rating' => 4.8, 'reviewsCount' => 67, 'questionsCount' => 4,
                'inStock' => true, 'badge' => null,
                'image' => '/assets/products/bily-slon/01-front.png',
                'gallery' => ['/assets/products/bily-slon/01-front.png'],
                'description' => 'Bílý slon — „Elephant" varieta charakteristická velkými listy.',
            ],
            [
                'slug' => 'kratom-extrakt-10ml-zeleny',
                'name' => 'Kratom Extrakt 10 ml zelený', 'short' => 'Tekutý extrakt 12 % MIT',
                'color' => 'zeleny', 'strain' => 'maeng-da', 'form' => 'extrakt',
                'origin' => 'Borneo, Indonésie', 'grind' => 'vodný extrakt',
                'mitragynin' => '12,0', 'h7mg' => '0,11', 'purity' => '99,5',
                'batch' => 'VD-2026-X02', 'testedAt' => '11. 03. 2026',
                'price25' => 490, 'price50' => 0,
                'unit' => 'ml', 'unitSize' => 10,
                'rating' => 4.7, 'reviewsCount' => 74, 'questionsCount' => 11,
                'inStock' => true, 'badge' => ['variant' => 'express', 'label' => 'EXPRESS 180'],
                'image' => '/assets/products/kratom-extrakt-zeleny-10ml/101-front.png',
                'gallery' => ['/assets/products/kratom-extrakt-zeleny-10ml/101-front.png'],
                'description' => 'Koncentrovaný vodný extrakt z listu zelené Maeng Da. Obsah mitragyninu 12 %.',
            ],
        ];
    }
}
