<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['comment', 'like', 'follow']);

        return [
            'user_id' => User::factory(),
            'actor_id' => User::factory(),
            'type' => $type,
            'message' => match ($type) {
                'comment' => 'commented on your post',
                'like' => 'liked your post',
                default => 'started following you',
            },
            'url' => '/episodes/infinite-scroll',
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn (): array => [
            'read_at' => now()->subMinutes(fake()->numberBetween(1, 60)),
        ]);
    }

    public function unread(): static
    {
        return $this->state([
            'read_at' => null,
        ]);
    }
}
