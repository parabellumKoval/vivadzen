<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'role' => 'admin',
        ]);

        Sanctum::actingAs($admin, ['admin']);
    }

    public function test_index_lists_users_with_counts_and_search(): void
    {
        $user = User::create(['name' => 'Karel', 'email' => 'karel@example.com', 'password' => 'password123']);
        Order::create([
            'user_id' => $user->id, 'public_id' => 'VZ-2026-0009', 'status' => 'received',
            'email' => 'karel@example.com', 'phone' => '+420', 'first_name' => 'K', 'last_name' => 'K',
            'street' => 'S', 'city' => 'C', 'zip' => '10000', 'country' => 'CZ',
            'delivery_method' => 'courier', 'payment_method' => 'card',
            'subtotal' => 100, 'total' => 100, 'items_count' => 1,
        ]);

        $this->getJson('/admin-api/users?q=karel')
            ->assertOk()
            ->assertJsonPath('data.data.0.email', 'karel@example.com')
            ->assertJsonPath('data.data.0.orders_count', 1);
    }

    public function test_show_includes_orders_matched_by_email(): void
    {
        $user = User::create(['name' => 'Guest', 'email' => 'guest@example.com', 'password' => 'password123']);
        // Guest order (no user_id) but same e-mail.
        Order::create([
            'public_id' => 'VZ-2026-0010', 'status' => 'received',
            'email' => 'guest@example.com', 'phone' => '+420', 'first_name' => 'G', 'last_name' => 'G',
            'street' => 'S', 'city' => 'C', 'zip' => '10000', 'country' => 'CZ',
            'delivery_method' => 'courier', 'payment_method' => 'card',
            'subtotal' => 50, 'total' => 50, 'items_count' => 1,
        ]);

        $this->getJson("/admin-api/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('orders.0.public_id', 'VZ-2026-0010');
    }

    public function test_block_and_unblock(): void
    {
        $user = User::create(['name' => 'B', 'email' => 'b@example.com', 'password' => 'password123']);

        $this->postJson("/admin-api/users/{$user->id}/block")->assertOk();
        $this->assertNotNull($user->fresh()->blocked_at);

        $this->postJson("/admin-api/users/{$user->id}/unblock")->assertOk();
        $this->assertNull($user->fresh()->blocked_at);
    }

    public function test_update_and_delete(): void
    {
        $user = User::create(['name' => 'Old', 'email' => 'old@example.com', 'password' => 'password123']);

        $this->putJson("/admin-api/users/{$user->id}", [
            'name' => 'New Name', 'email' => 'new@example.com', 'phone' => '+420777',
        ])->assertOk()->assertJsonPath('data.name', 'New Name');

        $this->deleteJson("/admin-api/users/{$user->id}")->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
