<?php

namespace Database\Seeders;

use App\Models\Meeting;
use App\Models\Notification;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $demoUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $notificationActors = User::factory()->createMany([
            ['name' => 'Alex Rivera', 'email' => 'alex@example.com'],
            ['name' => 'Maya Chen', 'email' => 'maya@example.com'],
            ['name' => 'Jordan Bell', 'email' => 'jordan@example.com'],
            ['name' => 'Sam Okafor', 'email' => 'sam@example.com'],
        ]);

        Meeting::factory()->create([
            'title' => 'Weekly product sync',
            'notes' => 'Share updates, surface blockers, and agree on the next small step.',
        ]);

        Tag::factory()->createMany([
            ['name' => 'Art'],
            ['name' => 'Gaming'],
            ['name' => 'Music'],
            ['name' => 'Photography'],
            ['name' => 'Reading'],
            ['name' => 'Writing'],
        ]);

        Post::factory()
            ->count(24)
            ->sequence(function (Sequence $sequence): array {
                $category = ['technology', 'lifestyle', 'design'][$sequence->index % 3];

                return [
                    'title' => Str::headline($category).' field note '.($sequence->index + 1),
                    'excerpt' => "A practical {$category} post with useful UI details for the feed demo.",
                    'category' => $category,
                ];
            })
            ->create();

        Notification::factory()
            ->count(12)
            ->for($demoUser, 'user')
            ->state(fn (): array => [
                'actor_id' => $notificationActors->random()->id,
            ])
            ->sequence(function (Sequence $sequence): array {
                return [
                    'read_at' => $sequence->index < 5
                        ? null
                        : now()->subMinutes(($sequence->index + 1) * 4),
                ];
            })
            ->create();
    }
}
