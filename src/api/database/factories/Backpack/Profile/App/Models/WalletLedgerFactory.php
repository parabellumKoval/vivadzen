<?php

namespace Database\Factories\Backpack\Profile\App\Models;

use Backpack\Profile\app\Models\WalletLedger;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Backpack\Profile\app\Models\WalletLedger>
 */
class WalletLedgerFactory extends Factory
{
    protected $model = WalletLedger::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['credit', 'debit', 'hold', 'release', 'capture'];
        $referenceTypes = [
            'referral_reward',
            'review_reward', 
            'order_reward',
            'withdrawal',
            'order',
            'bonus',
            'refund',
            'fee'
        ];
        $currencies = ['VIVAPOINTS', 'USD', 'RUB'];

        return [
            'user_id' => \Profile::userModel()::factory(),
            'type' => $this->faker->randomElement($types),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'currency' => $this->faker->randomElement($currencies),
            'reference_type' => $this->faker->randomElement($referenceTypes),
            'reference_id' => (string) $this->faker->numberBetween(1, 999999),
            'meta' => [
                'test_data' => true,
                'description' => $this->faker->sentence(),
                'additional_info' => $this->faker->words(3, true)
            ],
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => function (array $attributes) {
                return $attributes['created_at'];
            },
        ];
    }

    /**
     * Indicate that the ledger entry is a credit operation.
     */
    public function credit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'credit',
            'reference_type' => $this->faker->randomElement([
                'referral_reward',
                'review_reward',
                'order_reward',
                'bonus',
                'refund'
            ]),
        ]);
    }

    /**
     * Indicate that the ledger entry is a debit operation.
     */
    public function debit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'debit',
            'reference_type' => $this->faker->randomElement([
                'withdrawal',
                'order',
                'fee'
            ]),
        ]);
    }

    /**
     * Indicate that the ledger entry is a hold operation.
     */
    public function hold(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'hold',
            'reference_type' => $this->faker->randomElement(['withdrawal', 'order']),
        ]);
    }

    /**
     * Indicate that the ledger entry is for referral rewards.
     */
    public function referralReward(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'credit',
            'reference_type' => 'referral_reward',
            'meta' => array_merge($attributes['meta'] ?? [], [
                'reward_event_id' => $this->faker->numberBetween(1, 1000),
                'trigger' => 'review.published',
                'review_id' => $this->faker->numberBetween(1, 500)
            ]),
        ]);
    }

    /**
     * Indicate that the ledger entry is for withdrawal.
     */
    public function withdrawal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $this->faker->randomElement(['hold', 'capture', 'release']),
            'reference_type' => 'withdrawal',
            'meta' => array_merge($attributes['meta'] ?? [], [
                'withdrawal_method' => $this->faker->randomElement(['bank_transfer', 'paypal', 'crypto']),
                'bank_account' => '****' . $this->faker->numberBetween(1000, 9999)
            ]),
        ]);
    }
}