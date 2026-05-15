<?php

namespace Tests\Feature\Reviews;

use Backpack\Reviews\app\Console\Commands\GenerateProductReviews;
use Backpack\Reviews\app\Services\ReviewProductTargetResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionProperty;
use ReflectionMethod;
use Tests\TestCase;

class GenerateProductReviewsSelectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('ak_category_product');
        Schema::dropIfExists('ak_attribute_product');
        Schema::dropIfExists('ak_product_regional_contents');
        Schema::dropIfExists('ak_product_categories');
        Schema::dropIfExists('ak_supplier_product');
        Schema::dropIfExists('ak_suppliers');
        Schema::dropIfExists('ak_products');

        Schema::create('ak_products', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('name')->nullable();
            $table->json('excerpt')->nullable();
            $table->json('content')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();
        });

        Schema::create('ak_product_categories', function (Blueprint $table): void {
            $table->id();
            $table->json('name')->nullable();
            $table->timestamps();
        });

        Schema::create('ak_category_product', function (Blueprint $table): void {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('category_id');
        });

        Schema::create('ak_attribute_product', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('attribute_id')->nullable();
            $table->unsignedBigInteger('attribute_value_id')->nullable();
            $table->string('value')->nullable();
            $table->json('value_trans')->nullable();
            $table->timestamps();
        });

        Schema::create('ak_suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('ak_supplier_product', function (Blueprint $table): void {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('supplier_id');
            $table->integer('in_stock')->nullable();
            $table->boolean('is_active')->nullable();
            $table->string('barcode')->nullable();
            $table->string('code')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('old_price', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('ak_product_regional_contents', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('country_code', 2)->nullable();
            $table->json('content')->nullable();
            $table->json('excerpt')->nullable();
            $table->json('merchant_content')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('ak_reviews');
        Schema::create('ak_reviews', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('reviewable_type');
            $table->unsignedBigInteger('reviewable_id');
            $table->boolean('is_moderated')->default(false);
            $table->integer('rating')->nullable();
            $table->text('text')->nullable();
            $table->json('extras')->nullable();
            $table->string('lang', 8)->nullable();
            $table->string('country', 2)->nullable();
            $table->timestamps();
        });
    }

    public function test_all_mode_limit_is_applied_after_canonical_product_deduplication(): void
    {
        $now = now();

        DB::table('ak_products')->insert([
            ['id' => 1, 'parent_id' => null, 'brand_id' => null, 'is_active' => true, 'name' => json_encode(['en' => 'Parent 1'], JSON_UNESCAPED_UNICODE), 'slug' => 'parent-1', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'parent_id' => 1, 'brand_id' => null, 'is_active' => true, 'name' => json_encode(['en' => 'Variant 1'], JSON_UNESCAPED_UNICODE), 'slug' => 'variant-1', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'parent_id' => null, 'brand_id' => null, 'is_active' => true, 'name' => json_encode(['en' => 'Parent 2'], JSON_UNESCAPED_UNICODE), 'slug' => 'parent-2', 'created_at' => $now, 'updated_at' => $now],
        ]);

        $command = app(GenerateProductReviews::class);
        $property = new ReflectionProperty($command, 'productTargetResolver');
        $property->setAccessible(true);
        $property->setValue($command, app(ReviewProductTargetResolver::class));

        $method = new ReflectionMethod($command, 'getProducts');
        $method->setAccessible(true);

        $products = $method->invoke($command, null, [], null, null, true, null, 2);

        $this->assertCount(2, $products);
        $this->assertSame([1, 3], $products->pluck('id')->all());
    }

    public function test_reserved_reviewer_owner_ids_respect_duplicate_prevention_option(): void
    {
        $now = now();

        DB::table('ak_products')->insert([
            ['id' => 1, 'parent_id' => null, 'brand_id' => null, 'is_active' => true, 'name' => json_encode(['en' => 'Product'], JSON_UNESCAPED_UNICODE), 'slug' => 'product', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('ak_reviews')->insert([
            'owner_id' => 77,
            'parent_id' => null,
            'reviewable_type' => 'App\\Models\\Product',
            'reviewable_id' => 1,
            'is_moderated' => false,
            'rating' => 5,
            'text' => 'Existing review',
            'extras' => json_encode([], JSON_UNESCAPED_UNICODE),
            'lang' => 'en',
            'country' => 'US',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $command = app(GenerateProductReviews::class);
        $resolverProperty = new ReflectionProperty($command, 'productTargetResolver');
        $resolverProperty->setAccessible(true);
        $resolverProperty->setValue($command, app(ReviewProductTargetResolver::class));

        $preventionProperty = new ReflectionProperty($command, 'preventDuplicateReviewers');
        $preventionProperty->setAccessible(true);

        $method = new ReflectionMethod($command, 'resolveReservedReviewerOwnerIds');
        $method->setAccessible(true);

        $product = \App\Models\Product::query()->findOrFail(1);

        $preventionProperty->setValue($command, true);
        $this->assertSame([77], $method->invoke($command, $product));

        $preventionProperty->setValue($command, false);
        $this->assertSame([], $method->invoke($command, $product));
    }

    public function test_boolean_option_normalizer_accepts_zero_for_duplicate_prevention(): void
    {
        $command = app(GenerateProductReviews::class);

        $method = new ReflectionMethod($command, 'normalizeBooleanOption');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($command, '0', true));
        $this->assertTrue($method->invoke($command, '1', false));
    }

    public function test_product_description_combines_excerpt_and_full_content(): void
    {
        $now = now();

        DB::table('ak_products')->insert([
            'id' => 10,
            'parent_id' => null,
            'brand_id' => null,
            'is_active' => true,
            'name' => json_encode(['cs' => 'Kava'], JSON_UNESCAPED_UNICODE),
            'excerpt' => json_encode(['cs' => 'Krátký benefit\\nJasná mysl'], JSON_UNESCAPED_UNICODE),
            'content' => json_encode(['cs' => 'Fallback content should not be first.'], JSON_UNESCAPED_UNICODE),
            'slug' => 'kava',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('ak_product_regional_contents')->insert([
            'product_id' => 10,
            'country_code' => 'cz',
            'excerpt' => json_encode(['cs' => 'Regionální krátký text\\nRelax'], JSON_UNESCAPED_UNICODE),
            'content' => json_encode(['cs' => 'Plný&nbsp;popis<br>Chuť, příprava a večerní použití.'], JSON_UNESCAPED_UNICODE),
            'merchant_content' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $command = app(GenerateProductReviews::class);
        $method = new ReflectionMethod($command, 'resolveProductDescription');
        $method->setAccessible(true);

        $product = \App\Models\Product::query()->with('regionalContents')->findOrFail(10);
        $description = $method->invoke($command, $product, 'cs', 'CZ');

        $this->assertStringContainsString('Regionální krátký text Relax', $description);
        $this->assertStringContainsString('Plný popis Chuť, příprava a večerní použití.', $description);
        $this->assertStringNotContainsString('\\n', $description);
        $this->assertStringNotContainsString('&nbsp;', $description);
    }

    public function test_review_briefs_are_created_per_user_with_distinct_angles(): void
    {
        $command = app(GenerateProductReviews::class);
        $method = new ReflectionMethod($command, 'buildReviewBriefs');
        $method->setAccessible(true);

        $briefs = $method->invoke($command, 5);

        $this->assertCount(5, $briefs);
        $this->assertCount(5, collect($briefs)->pluck('angle')->unique());
        $this->assertSame(
            'do not use a repeated contrast formula like "good, but ..." unless the angle explicitly requires a minor flaw',
            $briefs[0]['avoid_patterns'][1]
        );
    }

    public function test_generated_reviews_are_post_processed_into_distinct_styles(): void
    {
        $command = app(GenerateProductReviews::class);

        $briefs = [
            ['style_mode' => 'one_word'],
            ['style_mode' => 'emoji_spam'],
            ['style_mode' => 'sloppy_lowercase'],
            ['style_mode' => 'camel_case'],
            ['style_mode' => 'punctuation_spam'],
            ['style_mode' => 'typo_heavy'],
        ];
        $reviews = array_map(
            fn (int $index) => [
                'user_index' => $index,
                'text' => 'Product is good and effective, but the taste is specific and I needed time to get used to it.',
                'advantages' => ['good'],
                'flaws' => ['taste'],
            ],
            range(0, 5)
        );

        $method = new ReflectionMethod($command, 'styleGeneratedReviews');
        $method->setAccessible(true);

        $styled = $method->invoke($command, $reviews, $briefs, 'en');

        $this->assertLessThanOrEqual(2, str_word_count($styled[0]['text']));
        $this->assertMatchesRegularExpression('/😍|🔥|:D|😅|💚|\\)\\)\\)\\)/u', $styled[1]['text']);
        $this->assertSame(mb_strtolower($styled[2]['text']), $styled[2]['text']);
        $this->assertMatchesRegularExpression('/^[a-z]+[A-Z]/', $styled[3]['text']);
        $this->assertMatchesRegularExpression('/!!!|\\?\\?\\)|\\.\\.\\.!!!|\\)\\)\\)$/', $styled[4]['text']);
        $this->assertStringContainsString('))', $styled[5]['text']);
        $this->assertSame([], $styled[0]['advantages']);
        $this->assertSame([], $styled[1]['flaws']);
    }
}
