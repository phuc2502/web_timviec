<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Verify that the job details page loads successfully for all users.
     */
    public function test_job_details_page_loads_for_all_users(): void
    {
        $employer = \App\Models\User::factory()->create([
            'user_type' => 'employer',
        ]);

        $employee = \App\Models\User::factory()->create([
            'user_type' => 'employee',
        ]);

        $listing = \App\Models\Listing::create([
            'user_id' => $employer->id,
            'title' => 'Senior PHP Engineer',
            'slug' => 'senior-php-engineer-' . \Illuminate\Support\Str::random(5),
            'application_close_date' => now()->addDays(30),
        ]);

        // 1. Guest views page
        $response = $this->get(route('job.show', $listing->slug));
        $response->assertStatus(200);

        // 2. Employer views page
        $response = $this->actingAs($employer)
            ->get(route('job.show', $listing->slug));
        $response->assertStatus(200);

        // 3. Employee views page
        $response = $this->actingAs($employee)
            ->get(route('job.show', $listing->slug));
        $response->assertStatus(200);
    }
}
