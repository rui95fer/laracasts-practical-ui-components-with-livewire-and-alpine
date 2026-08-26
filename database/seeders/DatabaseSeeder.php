<?php

namespace Database\Seeders;

use App\Models\Meeting;
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

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
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
    }
}
