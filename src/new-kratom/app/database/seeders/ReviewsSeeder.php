<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\ProductReview;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Демо-отзывы и Q&A. Отзыв привязан только к товару (без модификации/упаковки).
 * Раскидываем отзывы по нескольким товарам, чтобы на главной слайдер мог
 * чередовать отзывы о разных товарах.
 */
class ReviewsSeeder extends Seeder
{
    public function run(): void
    {
        // Подробные отзывы для флагмана.
        $this->seedReviews('cervena-maeng-da', [
            ['author_name' => 'Pavla N.', 'rating' => 5, 'verified_purchase' => true,  'helpful_count' => 12, 'days' => 3,
             'body' => 'Velmi rychlé doručení, balení odpovídá popisu. Šarže s vlastním COA — to jsme jinde neviděli. Profesionální přístup.'],
            ['author_name' => 'Martin K.', 'rating' => 5, 'verified_purchase' => true,  'helpful_count' => 8,  'days' => 7,
             'body' => 'Stabilní kvalita, jemné mletí. Lab-test data jsou v souladu s tím, co je deklarováno na obalu.'],
            ['author_name' => 'Jana V.',   'rating' => 4, 'verified_purchase' => false, 'helpful_count' => 5,  'days' => 14,
             'body' => 'Spokojenost. Express doručení v Praze fungovalo přesně, jak slibovali — do 2 hodin u dveří.'],
            ['author_name' => 'Tomáš H.',  'rating' => 5, 'verified_purchase' => true,  'helpful_count' => 17, 'days' => 21,
             'body' => 'Používám pravidelně už rok, kvalita je konzistentní napříč šaržemi. Doporučuji.'],
            ['author_name' => 'Klára M.',  'rating' => 5, 'verified_purchase' => true,  'helpful_count' => 3,  'days' => 28,
             'body' => 'První nákup, vše proběhlo hladce. Detail informace o šarži v balíčku — super přístup.'],
        ]);

        // Отзывы по другим товарам — для чередования на главной.
        $this->seedReviews('zelena-maeng-da', [
            ['author_name' => 'Ondřej P.', 'rating' => 5, 'verified_purchase' => true,  'helpful_count' => 6, 'days' => 4,
             'body' => 'Příjemná zelená odrůda, vyvážený profil. Balení precizní, doručení druhý den.'],
            ['author_name' => 'Lucie K.',  'rating' => 4, 'verified_purchase' => true,  'helpful_count' => 2, 'days' => 17,
             'body' => 'Kvalita odpovídá ceně, COA dostupné online. Vrátím se pro větší balení.'],
        ]);

        $this->seedReviews('bila-maeng-da', [
            ['author_name' => 'Filip D.',  'rating' => 5, 'verified_purchase' => true,  'helpful_count' => 9, 'days' => 2,
             'body' => 'Bílá Maeng Da je svěží a čistá. Jemné mletí, žádné hrudky. Top obchod.'],
            ['author_name' => 'Anna S.',   'rating' => 5, 'verified_purchase' => false, 'helpful_count' => 4, 'days' => 11,
             'body' => 'Objednávka dorazila rychle, vše zabaleno diskrétně. Lab-testy publikované otevřeně.'],
        ]);

        $this->seedReviews('zelena-sumatra', [
            ['author_name' => 'Petr M.',   'rating' => 5, 'verified_purchase' => true,  'helpful_count' => 7, 'days' => 5,
             'body' => 'Sumatra mě překvapila kvalitou. Konzistentní šarže, férový přístup.'],
            ['author_name' => 'Veronika H.','rating' => 4, 'verified_purchase' => true, 'helpful_count' => 1, 'days' => 23,
             'body' => 'Spolehlivá volba, doručení v pořádku. Doporučuji vyzkoušet.'],
        ]);

        $this->seedReviews('bily-slon', [
            ['author_name' => 'Marek J.',  'rating' => 5, 'verified_purchase' => true,  'helpful_count' => 5, 'days' => 6,
             'body' => 'Bílý slon má skvělý poměr cena/kvalita. Transparentní COA, rychlé doručení.'],
        ]);

        $this->seedReviews('kratom-extrakt-10ml-zeleny', [
            ['author_name' => 'Daniel R.', 'rating' => 5, 'verified_purchase' => true,  'helpful_count' => 11, 'days' => 8,
             'body' => 'Extrakt je silný a čistý, přesně jak má být. Profesionální balení, COA v balíčku.'],
        ]);

        $this->seedQuestions();
    }

    /**
     * @param array<int, array<string, mixed>> $reviews
     */
    private function seedReviews(string $slug, array $reviews): void
    {
        $product = Product::where('slug', $slug)->first();
        if (! $product) {
            return;
        }

        foreach ($reviews as $r) {
            ProductReview::firstOrCreate(
                [
                    'product_id'  => $product->id,
                    'author_name' => $r['author_name'],
                    'body'        => $r['body'],
                ],
                [
                    'rating'            => $r['rating'],
                    'verified_purchase' => $r['verified_purchase'],
                    'helpful_count'     => $r['helpful_count'],
                    'status'            => 'approved',
                    'published_at'      => Carbon::now()->subDays($r['days']),
                ]
            );
        }

        $approved = $product->reviews()->public();
        $product->update([
            'reviews_count' => $approved->count() ?: count($reviews),
            'rating'        => round($approved->avg('rating') ?: 5, 1),
        ]);
    }

    private function seedQuestions(): void
    {
        $product = Product::where('slug', 'cervena-maeng-da')->first();
        if (! $product) {
            return;
        }

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
