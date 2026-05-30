<?php

namespace Tests\Feature\Account;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_is_logged_in(): void
    {
        Notification::fake();

        $response = $this->postJson('/auth/register', [
            'name' => 'Jan Novák',
            'email' => 'jan@example.com',
            'password' => 'supersecret',
            'password_confirmation' => 'supersecret',
            'marketing_consent' => true,
        ]);

        $response->assertCreated()->assertJson(['ok' => true]);
        $this->assertDatabaseHas('users', ['email' => 'jan@example.com', 'marketing_consent' => true]);
        $this->assertAuthenticated();

        $user = User::where('email', 'jan@example.com')->first();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_login_rejects_wrong_password_and_accepts_correct(): void
    {
        $user = User::create([
            'name' => 'Eva',
            'email' => 'eva@example.com',
            'password' => 'correct-horse',
        ]);

        $this->postJson('/auth/login', ['email' => 'eva@example.com', 'password' => 'nope'])
            ->assertStatus(422);

        $this->postJson('/auth/login', ['email' => 'eva@example.com', 'password' => 'correct-horse'])
            ->assertOk()->assertJson(['ok' => true]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_blocked_user_cannot_login(): void
    {
        $user = User::create([
            'name' => 'Blocked',
            'email' => 'blocked@example.com',
            'password' => 'password123',
        ]);
        $user->forceFill(['blocked_at' => now()])->save();

        $this->postJson('/auth/login', ['email' => 'blocked@example.com', 'password' => 'password123'])
            ->assertStatus(422);
        $this->assertGuest();
    }

    public function test_address_crud_and_default_promotion(): void
    {
        $user = User::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'password123']);
        $this->actingAs($user);

        // First address becomes default automatically.
        $first = $this->postJson('/ucet/adresy', [
            'first_name' => 'Jan', 'last_name' => 'Novák',
            'street' => 'Hlavní 1', 'city' => 'Praha', 'zip' => '11000',
        ])->assertCreated()->json('data');
        $this->assertTrue($first['is_default']);

        // Second one, explicitly default → first loses default.
        $second = $this->postJson('/ucet/adresy', [
            'first_name' => 'Petr', 'last_name' => 'Svoboda',
            'street' => 'Vedlejší 2', 'city' => 'Brno', 'zip' => '60200',
            'is_default' => true,
        ])->assertCreated()->json('data');

        $this->assertDatabaseHas('addresses', ['id' => $second['id'], 'is_default' => true]);
        $this->assertDatabaseHas('addresses', ['id' => $first['id'], 'is_default' => false]);

        // Deleting the default promotes the remaining address.
        $this->deleteJson("/ucet/adresy/{$second['id']}")->assertOk();
        $this->assertDatabaseHas('addresses', ['id' => $first['id'], 'is_default' => true]);
    }

    public function test_address_owner_isolation(): void
    {
        $owner = User::create(['name' => 'O', 'email' => 'o@example.com', 'password' => 'password123']);
        $intruder = User::create(['name' => 'I', 'email' => 'i@example.com', 'password' => 'password123']);

        $address = $owner->addresses()->create([
            'first_name' => 'X', 'last_name' => 'Y', 'street' => 'S', 'city' => 'C', 'zip' => '10000',
        ]);

        $this->actingAs($intruder)
            ->deleteJson("/ucet/adresy/{$address->id}")
            ->assertForbidden();
    }

    public function test_social_user_can_set_password(): void
    {
        $user = User::create(['name' => 'Soc', 'email' => 'soc@example.com', 'password' => null]);
        $this->assertFalse($user->hasPassword());

        $this->actingAs($user)
            ->post('/ucet/heslo', ['password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass'])
            ->assertRedirect();

        $this->assertTrue($user->fresh()->hasPassword());
    }

    public function test_email_change_resets_verification_and_notifies(): void
    {
        Notification::fake();
        $user = User::create([
            'name' => 'M', 'email' => 'old@example.com',
            'password' => 'password123', 'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->post('/ucet/email', ['email' => 'new@example.com'])
            ->assertRedirect();

        $fresh = $user->fresh();
        $this->assertSame('new@example.com', $fresh->email);
        $this->assertNull($fresh->email_verified_at);
        Notification::assertSentTo($fresh, VerifyEmail::class);
    }

    public function test_order_detail_is_scoped_to_owner(): void
    {
        $owner = User::create(['name' => 'O', 'email' => 'owner@example.com', 'password' => 'password123']);
        $other = User::create(['name' => 'X', 'email' => 'x@example.com', 'password' => 'password123']);

        $order = Order::create([
            'user_id' => $owner->id, 'public_id' => 'VZ-2026-0001', 'status' => 'received',
            'email' => 'owner@example.com', 'phone' => '+420', 'first_name' => 'O', 'last_name' => 'O',
            'street' => 'S', 'city' => 'C', 'zip' => '10000', 'country' => 'CZ',
            'delivery_method' => 'courier', 'payment_method' => 'card',
            'subtotal' => 100, 'total' => 100, 'items_count' => 1,
        ]);

        $this->actingAs($owner)->getJson("/ucet/objednavky/{$order->public_id}")
            ->assertOk()->assertJsonPath('data.public_id', 'VZ-2026-0001');

        $this->actingAs($other)->getJson("/ucet/objednavky/{$order->public_id}")
            ->assertNotFound();
    }
}
