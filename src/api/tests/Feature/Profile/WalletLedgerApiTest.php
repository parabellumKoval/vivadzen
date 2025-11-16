<?php

namespace Tests\Feature\Profile;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Backpack\Profile\app\Models\WalletLedger;

class WalletLedgerApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Создаем пользователя для тестов
        $userModel = \Profile::userModel();
        $this->user = $userModel::factory()->create();
        
        // Создаем профиль для пользователя
        $this->user->profile()->create([
            'first_name' => 'Test',
            'last_name' => 'User'
        ]);
    }

    public function test_unauthorized_access_returns_401()
    {
        $response = $this->getJson('/api/profile/wallet/ledger');
        
        $response->assertStatus(401);
    }

    public function test_can_get_wallet_ledger_history()
    {
        Sanctum::actingAs($this->user);
        
        // Создаем несколько записей в ledger
        WalletLedger::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'type' => 'credit',
            'amount' => 100.00,
            'currency' => 'VIVAPOINTS',
            'reference_type' => 'referral_reward',
            'reference_id' => '123'
        ]);
        
        $response = $this->getJson('/api/profile/wallet/ledger');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'type',
                            'type_label',
                            'amount' => [
                                'value',
                                'formatted',
                                'currency'
                            ],
                            'reference' => [
                                'type',
                                'id'
                            ],
                            'operation_details',
                            'created_at'
                        ]
                    ],
                    'meta' => [
                        'pagination' => [
                            'current_page',
                            'per_page',
                            'total',
                            'last_page'
                        ]
                    ]
                ]);
                
        $this->assertEquals(3, $response->json('meta.pagination.total'));
    }

    public function test_can_filter_by_type()
    {
        Sanctum::actingAs($this->user);
        
        // Создаем записи разных типов
        WalletLedger::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'credit',
            'amount' => 100.00,
            'currency' => 'VIVAPOINTS'
        ]);
        
        WalletLedger::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'debit',
            'amount' => 50.00,
            'currency' => 'VIVAPOINTS'
        ]);
        
        // Фильтруем только credit операции
        $response = $this->getJson('/api/profile/wallet/ledger?type=credit');
        
        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.pagination.total'));
        $this->assertEquals('credit', $response->json('data.0.type'));
    }

    public function test_can_filter_by_reference_type()
    {
        Sanctum::actingAs($this->user);
        
        // Создаем записи разных reference_type
        WalletLedger::factory()->create([
            'user_id' => $this->user->id,
            'reference_type' => 'referral_reward',
            'type' => 'credit'
        ]);
        
        WalletLedger::factory()->create([
            'user_id' => $this->user->id,
            'reference_type' => 'withdrawal',
            'type' => 'debit'
        ]);
        
        // Фильтруем только referral_reward
        $response = $this->getJson('/api/profile/wallet/ledger?reference_type=referral_reward');
        
        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.pagination.total'));
        $this->assertEquals('referral_reward', $response->json('data.0.reference.type'));
    }

    public function test_pagination_works()
    {
        Sanctum::actingAs($this->user);
        
        // Создаем 25 записей
        WalletLedger::factory()->count(25)->create([
            'user_id' => $this->user->id,
            'type' => 'credit'
        ]);
        
        // Запрашиваем первую страницу с лимитом 10
        $response = $this->getJson('/api/profile/wallet/ledger?per_page=10&page=1');
        
        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertEquals(10, count($data['data']));
        $this->assertEquals(1, $data['meta']['pagination']['current_page']);
        $this->assertEquals(10, $data['meta']['pagination']['per_page']);
        $this->assertEquals(25, $data['meta']['pagination']['total']);
        $this->assertEquals(3, $data['meta']['pagination']['last_page']);
    }

    public function test_validation_errors()
    {
        Sanctum::actingAs($this->user);
        
        // Тест с неверным типом
        $response = $this->getJson('/api/profile/wallet/ledger?type=invalid_type');
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['type']);
        
        // Тест с неверным per_page
        $response = $this->getJson('/api/profile/wallet/ledger?per_page=150');
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['per_page']);
        
        // Тест с неверной страницей
        $response = $this->getJson('/api/profile/wallet/ledger?page=0');
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['page']);
    }

    public function test_only_user_own_records_returned()
    {
        Sanctum::actingAs($this->user);
        
        // Создаем записи для текущего пользователя
        WalletLedger::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'type' => 'credit'
        ]);
        
        // Создаем записи для другого пользователя
        $otherUser = \Profile::userModel()::factory()->create();
        WalletLedger::factory()->count(3)->create([
            'user_id' => $otherUser->id,
            'type' => 'credit'
        ]);
        
        $response = $this->getJson('/api/profile/wallet/ledger');
        
        $response->assertStatus(200);
        // Должны вернуться только записи текущего пользователя
        $this->assertEquals(2, $response->json('meta.pagination.total'));
        
        foreach ($response->json('data') as $entry) {
            // Проверяем, что все записи принадлежат текущему пользователю
            // (косвенно, так как user_id не включен в ответ)
            $this->assertArrayHasKey('id', $entry);
        }
    }

    public function test_records_ordered_by_created_at_desc()
    {
        Sanctum::actingAs($this->user);
        
        // Создаем записи с разными временными метками
        $old = WalletLedger::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(2)
        ]);
        
        $new = WalletLedger::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDay()
        ]);
        
        $newest = WalletLedger::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()
        ]);
        
        $response = $this->getJson('/api/profile/wallet/ledger');
        
        $response->assertStatus(200);
        
        $data = $response->json('data');
        
        // Проверяем порядок - новые записи должны быть первыми
        $this->assertEquals($newest->id, $data[0]['id']);
        $this->assertEquals($new->id, $data[1]['id']);
        $this->assertEquals($old->id, $data[2]['id']);
    }
}