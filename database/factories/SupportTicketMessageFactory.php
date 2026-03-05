<?php

namespace Database\Factories;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicketMessage>
 */
class SupportTicketMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'support_ticket_id' => SupportTicket::factory(),
            'user_id' => User::factory()->tutor(),
            'body' => fake()->paragraph(),
        ];
    }

    /**
     * Create the message for a specific ticket.
     */
    public function forTicket(SupportTicket $ticket): static
    {
        return $this->state([
            'support_ticket_id' => $ticket->id,
        ]);
    }

    /**
     * Create the message as an admin reply.
     */
    public function adminReply(): static
    {
        return $this->state([
            'user_id' => User::factory()->admin(),
        ]);
    }
}
