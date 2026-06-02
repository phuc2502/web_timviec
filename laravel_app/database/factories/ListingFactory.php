<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    protected $model = Listing::class;

    public function definition(): array
    {
        $title = fake()->jobTitle();

        return [
            'user_id'                => User::factory()->employer(),
            'title'                  => $title,
            'slug'                   => Str::slug($title) . '-' . fake()->unique()->numerify('####'),
            'predes'                 => fake()->optional(0.8)->sentence(20),
            'description'            => fake()->paragraphs(3, true),
            'requirements'           => null,
            'benefits'               => null,
            'job_type'               => fake()->randomElement(['full-time', 'part-time', 'freelance', 'internship']),
            'work_mode'              => fake()->randomElement(['onsite', 'remote', 'hybrid']),
            'experience_years_min'   => null,
            'experience_years_max'   => null,
            'job_level'              => null,
            'address'                => fake()->city() . ', ' . fake()->country(),
            'salary'                 => fake()->randomElement([0, 5000000, 10000000, 15000000, 20000000, 30000000]),
            'feature_image'          => null,
            'application_close_date' => fake()->dateTimeBetween('+1 day', '+6 months')->format('Y-m-d'),
            'status'                 => 'open',
        ];
    }

    /**
     * Indicate that the listing is closed (expired deadline).
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'                 => 'closed',
            'application_close_date' => fake()->dateTimeBetween('-6 months', '-1 day')->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the listing has a past close date but status=open (effectively expired).
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'                 => 'open',
            'application_close_date' => fake()->dateTimeBetween('-6 months', '-1 day')->format('Y-m-d'),
        ]);
    }

    /**
     * Active listing — status=open and future close date.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'                 => 'open',
            'application_close_date' => fake()->dateTimeBetween('+1 day', '+6 months')->format('Y-m-d'),
        ]);
    }

    /**
     * Listing with a specific salary (non-zero, for salary filter tests).
     */
    public function withSalary(int $salary): static
    {
        return $this->state(fn (array $attributes) => [
            'salary' => $salary,
        ]);
    }

    /**
     * Listing with salary = 0 ("thỏa thuận").
     */
    public function negotiable(): static
    {
        return $this->state(fn (array $attributes) => [
            'salary' => 0,
        ]);
    }

    /**
     * Listing with experience range.
     */
    public function withExperience(int $min, int $max): static
    {
        return $this->state(fn (array $attributes) => [
            'experience_years_min' => $min,
            'experience_years_max' => $max,
        ]);
    }
}
