<?php

namespace Database\Factories;

use App\Enums\LedgerEntryType;
use App\Models\User;
use App\Models\WalletLedgerEntry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WalletLedgerEntry>
 */
class WalletLedgerEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'journal_uuid' => Str::uuid()->toString(),
            'owner_user_id' => User::factory(),
            'type' => fake()->randomElement([
                LedgerEntryType::Debit,
                LedgerEntryType::Credit,
            ]),
            'amount' => fake()->randomFloat(2, 100, 5000),
            'currency' => 'BDT',
            'reference_type' => 'invoice',
            'reference_id' => fake()->numberBetween(1, 1000),
            'counterparty_user_id' => User::factory(),
            'posted_at' => now(),
            'is_reversal' => false,
            'reverses_journal_uuid' => null,
            'metadata' => null,
        ];
    }
}
