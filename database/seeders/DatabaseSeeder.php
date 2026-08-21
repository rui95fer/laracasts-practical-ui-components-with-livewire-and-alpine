<?php

namespace Database\Seeders;

use App\Models\Meeting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
    }
}
