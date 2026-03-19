<?php

namespace Tests\Feature\Store;

use Backpack\Store\app\Models\Campaign;
use Backpack\Store\app\Services\Campaign\CampaignProductSyncService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CampaignProductSyncServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('ak_campaign_product');
        Schema::dropIfExists('ak_catalog');
        Schema::dropIfExists('ak_campaigns');

        Schema::create('ak_campaigns', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->string('name');
            $table->string('slug');
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->integer('priority')->default(0);
            $table->boolean('is_timed')->default(false);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->json('countries')->nullable();
            $table->string('product_source')->default('filters');
            $table->json('product_filters')->nullable();
            $table->json('manual_products')->nullable();
            $table->timestamps();
        });

        Schema::create('ak_catalog', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('country_code', 8);
            $table->boolean('is_available')->default(true);
            $table->json('category_ids')->nullable();
        });

        Schema::create('ak_campaign_product', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('product_id');
            $table->string('country_code', 8);
        });
    }

    public function test_sync_campaign_applies_nested_repeatable_filter_rules(): void
    {
        DB::table('ak_catalog')->insert([
            [
                'product_id' => 101,
                'country_code' => 'cz',
                'is_available' => 1,
                'category_ids' => json_encode([2]),
            ],
            [
                'product_id' => 202,
                'country_code' => 'cz',
                'is_available' => 1,
                'category_ids' => json_encode([7]),
            ],
            [
                'product_id' => 303,
                'country_code' => 'cz',
                'is_available' => 1,
                'category_ids' => json_encode([2, 9]),
            ],
        ]);

        $campaignId = DB::table('ak_campaigns')->insertGetId([
            'is_active' => 1,
            'name' => 'Category campaign',
            'slug' => 'category-campaign',
            'discount_percent' => 10,
            'priority' => 0,
            'is_timed' => 0,
            'product_source' => 'filters',
            'product_filters' => json_encode([
                [
                    'rule' => [
                        'key' => 'categories',
                        'categories' => [2],
                        'include_children' => false,
                        'direction' => 'include',
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $campaign = Campaign::query()->findOrFail($campaignId);

        app(CampaignProductSyncService::class)->syncCampaign($campaign, ['cz']);

        $this->assertSame(
            [101, 303],
            DB::table('ak_campaign_product')
                ->where('campaign_id', $campaignId)
                ->where('country_code', 'cz')
                ->orderBy('product_id')
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->all()
        );
    }
}
