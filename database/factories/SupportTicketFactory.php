<?php

namespace Database\Factories;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->tutor(),
            'subject' => fake()->sentence(),
            'category' => fake()->randomElement(TicketCategory::cases()),
            'priority' => TicketPriority::Medium,
            'status' => TicketStatus::Open,
            'assigned_to' => null,
            'closed_at' => null,
            'closed_by' => null,
        ];
    }

    /**
     * Indicate the ticket is open.
     */
    public function open(): static
    {
        return $this->state([
            'status' => TicketStatus::Open,
        ]);
    }

    /**
     * Indicate the ticket is in progress.
     */
    public function inProgress(): static
    {
        return $this->state([
            'status' => TicketStatus::InProgress,
        ]);
    }

    /**
     * Indicate the ticket is closed.
     */
    public function closed(): static
    {
        return $this->state([
            'status' => TicketStatus::Closed,
            'closed_at' => now(),
            'closed_by' => User::factory()->admin(),
        ]);
    }

    /**
     * Set the ticket priority to high.
     */
    public function highPriority(): static
    {
        return $this->state([
            'priority' => TicketPriority::High,
        ]);
    }

    /**
     * Set the ticket priority to urgent.
     */
    public function urgent(): static
    {
        return $this->state([
            'priority' => TicketPriority::Urgent,
        ]);
    }

    /**
     * Set a specific category.
     */
    public function withCategory(TicketCategory $category): static
    {
        return $this->state([
            'category' => $category,
        ]);
    }

    /**
     * Assign the ticket to an admin.
     */
    public function assignedTo(User $admin): static
    {
        return $this->state([
            'assigned_to' => $admin->id,
        ]);
    }

    /**
     * Create the ticket for a guardian user.
     */
    public function forGuardian(): static
    {
        return $this->state([
            'user_id' => User::factory()->guardian(),
        ]);
    }
}
