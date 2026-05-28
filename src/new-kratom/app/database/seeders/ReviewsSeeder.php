<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\ProductReview;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Демо-отзывы и Q&A для основных продуктов.
 * Менеджер потом может писать новые задним числом / запланировать на будущее
 * через админ-API. Все записи создаются approved + published_at в прошлом.
 */
class ReviewsSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::where('slug', 'cervena-maeng-da')->first();
        if (! $product) {
            return;
        }

        $reviews = [
            [
                'author_name' => 'Pavla N.',
                'rating' => 5,
                'package' => '25 g',
                'body' => 'Velmi rychlé doručení, balení odpovídá popisu. Šarže s vlastním COA — to jsme jinde neviděli. Profesionální přístup.',
                'verified_purchase' => true,
                'helpful_count' => 12,
                'published_at' => Carbon::now()->subDays(3),
            ],
            [
                'author_name' => 'Martin K.',
                'rating' => 5,
                'package' => '50 g',
                'body' => 'Stabilní kvalita, jemný mletí. Lab-test data jsou v souladu s tím, co je deklarováno na obalu.',
                'verified_purchase' => true,
                'helpful_count' => 8,
                'published_at' => Carbon::now()->subWeek(),
            ],
            [
                'author_name' => 'Jana V.',
                'rating' => 4,
                'package' => '25 g',
                'body' => 'Spokojenost. Express doručení v Praze fungovalo přesně, jak slibovali — do 2 hodin u dveří.',
                'verified_purchase' => false,
                'helpful_count' => 5,
                'published_at' => Carbon::now()->subDays(14),
            ],
            [
                'author_name' => 'Tomáš H.',
                'rating' => 5,
                'package' => '50 g',
                'body' => 'Použivám pravidelně už rok, kvalita je konzistentní napříč šaržemi. Doporučuji.',
                'verified_purchase' => true,
                'helpful_count' => 17,
                'published_at' => Carbon::now()->subDays(21),
            ],
            [
                'author_name' => 'Klára M.',
                'rating' => 5,
                'package' => '25 g',
                'body' => 'První nákup, vše proběhlo hladce. Detail informace o šarži v balíčku — super přístup.',
                'verified_purchase' => true,
                'helpful_count' => 3,
                'published_at' => Carbon::now()->subDays(28),
            ],
        ];

        foreach ($reviews as $r) {
            ProductReview::firstOrCreate(
                [
                    'product_id'  => $product->id,
                    'author_name' => $r['author_name'],
                    'body'        => $r['body'],
                ],
                array_merge($r, ['status' => 'approved'])
            );
        }

        $product->update([
            'reviews_count' => $product->reviews()->public()->count() ?: count($reviews),
            'rating'        => 4.9,
        ]);

        $questions = [
            [
                'author_name' => 'Tomáš L.',
                'question'    => 'Liší se šarže ' . ($product->batch ?? 'VD-2026-014') . ' nějak od předchozí šarže?',
                'answer'      => 'Šarže ' . ($product->batch ?? 'VD-2026-014') . ' má obsah mitragyninu '
                    . str_replace('.', ',', (string) ($product->mitragynin ?? '1.42'))
                    . ' %. Předchozí šarže měla 1,38 %. Rozdíly v rámci normy. Obě prošly stejnými laboratorními testy ve VŠCHT Praha.',
                'answered_by'   => 'Tým Vivadzen',
                'answered_at'   => Carbon::now()->subDays(11),
                'helpful_count' => 5,
                'published_at'  => Carbon::now()->subDays(12),
            ],
            [
                'author_name' => 'Petra S.',
                'question'    => 'Je možné objednat osobní odběr v Praze přes online platbu?',
                'answer'      => 'Ano. Při dokončení objednávky vyberete „Osobní odběr Praha" a zaplatíte online (kartou, Apple Pay, QR). Vyzvednutí pak proběhne bezhotovostně po ověření 18+.',
                'answered_by'   => 'Tým Vivadzen',
                'answered_at'   => Carbon::now()->subDays(19),
                'helpful_count' => 3,
                'published_at'  => Carbon::now()->subDays(20),
            ],
            [
                'author_name' => 'Lukáš J.',
                'question'    => 'Jak dlouho trvá doručení mimo Prahu?',
                'answer'      => 'Zásilkovna a PPL doručují do 24–48 hodin po expedici. Pokud objednáte do 14:00 v pracovní den, zásilka opouští sklad ještě téhož dne.',
                'answered_by'   => 'Tým Vivadzen',
                'answered_at'   => Carbon::now()->subDays(30),
                'helpful_count' => 9,
                'published_at'  => Carbon::now()->subDays(31),
            ],
        ];

        foreach ($questions as $q) {
            ProductQuestion::firstOrCreate(
                [
                    'product_id'  => $product->id,
                    'author_name' => $q['author_name'],
                    'question'    => $q['question'],
                ],
                array_merge($q, ['status' => 'approved'])
            );
        }

        $product->update([
            'questions_count' => $product->questions()->public()->count() ?: count($questions),
        ]);
    }
}
