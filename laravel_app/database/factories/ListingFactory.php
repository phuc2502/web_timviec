<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ListingFactory extends Factory
{
    protected $model = Listing::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $this->faker->jobTitle(),
            'description' => $this->faker->paragraphs(3, true),
            'address' => $this->faker->address(),
            'job_type' => $this->faker->randomElement(['full_time', 'part_time', 'contract', 'internship', 'freelance']),
            'level' => $this->faker->randomElement(['intern', 'junior', 'middle', 'senior', 'manager', 'director']),
            'salary_min' => 10000000,
            'salary_max' => 20000000,
            'is_negotiable' => false,
            'hide_salary' => false,
            'application_close_date' => now()->addDays(30)->toDateString(),
            'vacancy_count' => 1,
            'contact_email' => $this->faker->companyEmail(),
            'contact_phone' => '0987654321',
            'publish_mode' => 'immediate',
            'status' => 'active',
        ];
    }
}
