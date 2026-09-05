<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'excerpt' => fake()->paragraph(2),
            'content' => fake()->paragraphs(4, true),
            'category' => fake()->randomElement(['technology', 'lifestyle', 'design']),
            'published' => true,
        ];
    }
}
