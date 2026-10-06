<?php

namespace Database\Factories;

use App\Models\Film;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Film>
 */
class FilmFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => rtrim(fake()->sentence(random_int(2, 5)), '.'),
            'release_year' => fake()->numberBetween(1950, (int) date('Y')),
            'genre' => fake()->word()
        ];
    }
}
