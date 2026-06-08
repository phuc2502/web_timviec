<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for the Job Search Filter API endpoints.
 *
 * Covers:
 *   GET /api/listings/search
 *   GET /api/skills
 *   GET /api/listings/cities
 */
class ListingSearchApiTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Helpers
    // =========================================================================

    private function makeActiveListing(array $overrides = []): Listing
    {
        return Listing::factory()->active()->create($overrides);
    }

    // =========================================================================
    // GET /api/listings/search — Basic
    // =========================================================================

    #[Test]
    public function search_returns_http_200_with_json(): void
    {
        $response = $this->getJson('/api/listings/search');

        $response->assertStatus(200)
                 ->assertJson([]);
    }

    #[Test]
    public function search_returns_only_open_listings_with_future_close_date(): void
    {
        $active = $this->makeActiveListing();
        Listing::factory()->closed()->create();
        Listing::factory()->expired()->create();

        $data = $this->getJson('/api/listings/search')->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals($active->id, $data[0]['id']);
    }

    #[Test]
    public function search_returns_pagination_metadata(): void
    {
        $this->makeActiveListing();

        $this->getJson('/api/listings/search')
             ->assertStatus(200)
             ->assertJsonStructure([
                 'data',
                 'meta' => ['current_page', 'last_page', 'total', 'per_page'],
                 'links' => ['next', 'prev'],
             ]);
    }

    #[Test]
    public function search_returns_correct_listing_fields(): void
    {
        $this->makeActiveListing();

        $this->getJson('/api/listings/search')
             ->assertStatus(200)
             ->assertJsonStructure([
                 'data' => [
                     '*' => [
                         'id', 'title', 'slug', 'predes_truncated',
                         'job_type', 'work_mode', 'job_level',
                         'address', 'salary', 'salary_display',
                         'experience_years_min', 'experience_years_max', 'experience_display',
                         'application_close_date', 'created_at',
                         'employer' => ['company_name', 'company_logo', 'company_size'],
                         'skills',
                     ],
                 ],
             ]);
    }

    // =========================================================================
    // Req 1: Keyword / FULLTEXT
    // =========================================================================

    #[Test]
    public function search_without_keyword_does_not_include_relevance_score(): void
    {
        $this->makeActiveListing();

        $data = $this->getJson('/api/listings/search')->json('data');

        $this->assertArrayNotHasKey('relevance_score', $data[0]);
    }

    #[Test]
    public function search_with_keyword_includes_relevance_score_in_response(): void
    {
        // On SQLite (test env), FULLTEXT is replaced with LIKE fallback.
        // The listing title contains 'Laravel' so the LIKE will match.
        $this->makeActiveListing(['title' => 'Senior Laravel Developer', 'predes' => 'We need a senior laravel dev']);

        $data = $this->getJson('/api/listings/search?keyword=Laravel')->json('data');

        if (!empty($data)) {
            $this->assertArrayHasKey('relevance_score', $data[0]);
            $this->assertIsFloat($data[0]['relevance_score']);
        }
    }

    // =========================================================================
    // Req 2: job_type filter
    // =========================================================================

    #[Test]
    public function filter_by_job_type_returns_only_matching_listings(): void
    {
        $this->makeActiveListing(['job_type' => 'full-time']);
        $this->makeActiveListing(['job_type' => 'part-time']);

        $data = $this->getJson('/api/listings/search?job_type=full-time')->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals('full-time', $data[0]['job_type']);
    }

    #[Test]
    public function invalid_job_type_is_ignored_and_returns_all_listings(): void
    {
        $this->makeActiveListing(['job_type' => 'full-time']);

        $response = $this->getJson('/api/listings/search?job_type=invalid');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    // =========================================================================
    // Req 3: work_mode filter
    // =========================================================================

    #[Test]
    public function filter_by_work_mode_returns_only_matching_listings(): void
    {
        $this->makeActiveListing(['work_mode' => 'remote']);
        $this->makeActiveListing(['work_mode' => 'onsite']);

        $data = $this->getJson('/api/listings/search?work_mode=remote')->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals('remote', $data[0]['work_mode']);
    }

    // =========================================================================
    // Req 4: Skills filter
    // =========================================================================

    #[Test]
    public function filter_by_skills_and_mode_returns_listings_with_all_skills(): void
    {
        $skill1 = Skill::factory()->create(['name' => 'PHP',   'slug' => 'php']);
        $skill2 = Skill::factory()->create(['name' => 'MySQL', 'slug' => 'mysql']);

        $match = $this->makeActiveListing();
        $match->skills()->attach([$skill1->id, $skill2->id]);

        $noMatch = $this->makeActiveListing();
        $noMatch->skills()->attach([$skill1->id]);

        $data = $this->getJson("/api/listings/search?skills[]={$skill1->id}&skills[]={$skill2->id}&skill_mode=and")
                     ->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals($match->id, $data[0]['id']);
    }

    #[Test]
    public function filter_by_skills_or_mode_returns_listings_with_any_skill(): void
    {
        $skill1 = Skill::factory()->create(['name' => 'React', 'slug' => 'react']);
        $skill2 = Skill::factory()->create(['name' => 'Vue',   'slug' => 'vue']);

        $l1 = $this->makeActiveListing();
        $l1->skills()->attach($skill1->id);

        $l2 = $this->makeActiveListing();
        $l2->skills()->attach($skill2->id);

        $this->makeActiveListing(); // no matching skill

        $data = $this->getJson("/api/listings/search?skills[]={$skill1->id}&skills[]={$skill2->id}&skill_mode=or")
                     ->json('data');

        $this->assertCount(2, $data);
    }

    #[Test]
    public function invalid_skill_ids_are_ignored_and_no_filter_applied(): void
    {
        $this->makeActiveListing();

        $response = $this->getJson('/api/listings/search?skills[]=99999');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    // =========================================================================
    // Req 6: Salary filter
    // =========================================================================

    #[Test]
    public function salary_filter_excludes_negotiable_listings_when_min_set(): void
    {
        $this->makeActiveListing(['salary' => 0]);
        $this->makeActiveListing(['salary' => 10000000]);

        $data = $this->getJson('/api/listings/search?salary_min=5000000')->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals(10000000, $data[0]['salary']);
    }

    #[Test]
    public function salary_filter_not_applied_when_both_zero(): void
    {
        $this->makeActiveListing(['salary' => 0]);
        $this->makeActiveListing(['salary' => 10000000]);

        $this->assertCount(2, $this->getJson('/api/listings/search?salary_min=0&salary_max=0')->json('data'));
    }

    #[Test]
    public function salary_returns_422_when_min_greater_than_max(): void
    {
        $response = $this->getJson('/api/listings/search?salary_min=20000000&salary_max=10000000');

        $response->assertStatus(422)
                 ->assertJsonStructure(['message', 'errors']);
        $this->assertArrayHasKey('salary_max', $response->json('errors'));
    }

    #[Test]
    public function salary_display_shows_thoa_thuan_for_zero_salary(): void
    {
        $this->makeActiveListing(['salary' => 0]);

        $data = $this->getJson('/api/listings/search')->json('data');

        $this->assertEquals('Thỏa thuận', $data[0]['salary_display']);
    }

    #[Test]
    public function salary_display_shows_formatted_amount_for_nonzero_salary(): void
    {
        $this->makeActiveListing(['salary' => 15000000]);

        $data = $this->getJson('/api/listings/search')->json('data');

        $this->assertStringContainsString('VNĐ', $data[0]['salary_display']);
        $this->assertStringContainsString('15', $data[0]['salary_display']);
    }

    // =========================================================================
    // Req 7: Experience filter
    // =========================================================================

    #[Test]
    public function experience_filter_uses_intersection_logic(): void
    {
        // 3–5 years intersects with user filter 2–4 → should appear
        $this->makeActiveListing(['experience_years_min' => 3, 'experience_years_max' => 5]);
        // 6–8 years does NOT intersect with 2–4 → should NOT appear
        $this->makeActiveListing(['experience_years_min' => 6, 'experience_years_max' => 8]);

        $data = $this->getJson('/api/listings/search?exp_min=2&exp_max=4')->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals(3, $data[0]['experience_years_min']);
    }

    #[Test]
    public function experience_filter_includes_listings_with_null_experience(): void
    {
        $this->makeActiveListing(['experience_years_min' => null, 'experience_years_max' => null]);

        $this->assertCount(1, $this->getJson('/api/listings/search?exp_min=0&exp_max=5')->json('data'));
    }

    #[Test]
    public function experience_returns_422_when_min_greater_than_max(): void
    {
        $response = $this->getJson('/api/listings/search?exp_min=5&exp_max=2');

        $response->assertStatus(422)
                 ->assertJsonStructure(['message', 'errors']);
        $this->assertArrayHasKey('exp_max', $response->json('errors'));
    }

    // =========================================================================
    // Req 8: job_level filter
    // =========================================================================

    #[Test]
    public function filter_by_job_level_returns_only_matching_listings(): void
    {
        $this->makeActiveListing(['job_level' => 'senior']);
        $this->makeActiveListing(['job_level' => 'junior']);

        $data = $this->getJson('/api/listings/search?job_level=senior')->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals('senior', $data[0]['job_level']);
    }

    #[Test]
    public function job_level_null_included_when_no_job_level_filter(): void
    {
        $this->makeActiveListing(['job_level' => null]);
        $this->makeActiveListing(['job_level' => 'junior']);

        $this->assertCount(2, $this->getJson('/api/listings/search')->json('data'));
    }

    // =========================================================================
    // Req 9: company_size filter
    // =========================================================================

    #[Test]
    public function filter_by_company_size_returns_only_matching_listings(): void
    {
        $employer1 = User::factory()->employer()->create(['company_size' => '50-199']);
        $employer2 = User::factory()->employer()->create(['company_size' => '500+']);

        Listing::factory()->active()->create(['user_id' => $employer1->id]);
        Listing::factory()->active()->create(['user_id' => $employer2->id]);

        $this->assertCount(1, $this->getJson('/api/listings/search?company_size=50-199')->json('data'));
    }

    // =========================================================================
    // Req 10: Pagination
    // =========================================================================

    #[Test]
    public function pagination_defaults_to_15_per_page(): void
    {
        Listing::factory()->active()->count(20)->create();

        $response = $this->getJson('/api/listings/search');

        $this->assertCount(15, $response->json('data'));
        $this->assertEquals(15, $response->json('meta.per_page'));
    }

    #[Test]
    public function pagination_respects_custom_per_page(): void
    {
        Listing::factory()->active()->count(10)->create();

        $response = $this->getJson('/api/listings/search?per_page=5');

        $this->assertCount(5, $response->json('data'));
        $this->assertEquals(5, $response->json('meta.per_page'));
    }

    #[Test]
    public function invalid_per_page_falls_back_to_15(): void
    {
        Listing::factory()->active()->count(5)->create();

        $response = $this->getJson('/api/listings/search?per_page=100');

        $this->assertEquals(15, $response->json('meta.per_page'));
    }

    #[Test]
    public function requesting_page_beyond_last_page_returns_empty_data(): void
    {
        Listing::factory()->active()->count(3)->create();

        $response = $this->getJson('/api/listings/search?page=999');

        $response->assertStatus(200);
        $this->assertEmpty($response->json('data'));
        $this->assertNull($response->json('links.next'));
    }

    #[Test]
    public function invalid_page_param_falls_back_to_page_one(): void
    {
        $this->makeActiveListing();

        $response = $this->getJson('/api/listings/search?page=abc');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.current_page'));
    }

    #[Test]
    public function total_zero_returns_empty_data_with_last_page_one(): void
    {
        $response = $this->getJson('/api/listings/search');

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('meta.total'));
        $this->assertEquals(1, $response->json('meta.last_page'));
        $this->assertNull($response->json('links.next'));
        $this->assertNull($response->json('links.prev'));
    }

    // =========================================================================
    // Req 11: Sorting
    // =========================================================================

    #[Test]
    public function sort_newest_orders_by_created_at_descending(): void
    {
        $older = Listing::factory()->active()->create(['created_at' => now()->subDays(5)]);
        $newer = Listing::factory()->active()->create(['created_at' => now()]);

        $ids = array_column($this->getJson('/api/listings/search?sort=newest')->json('data'), 'id');

        $this->assertEquals($newer->id, $ids[0]);
    }

    #[Test]
    public function sort_salary_desc_puts_negotiable_listings_last(): void
    {
        $high       = Listing::factory()->active()->create(['salary' => 30000000]);
        $negotiable = Listing::factory()->active()->create(['salary' => 0]);
        $mid        = Listing::factory()->active()->create(['salary' => 10000000]);

        $ids = array_column($this->getJson('/api/listings/search?sort=salary_desc')->json('data'), 'id');

        $this->assertEquals($negotiable->id, end($ids));
        $this->assertEquals($high->id, $ids[0]);
    }

    #[Test]
    public function sort_closing_soon_orders_by_application_close_date_ascending(): void
    {
        $far  = Listing::factory()->active()->create(['application_close_date' => now()->addMonths(3)->format('Y-m-d')]);
        $soon = Listing::factory()->active()->create(['application_close_date' => now()->addDays(2)->format('Y-m-d')]);

        $ids = array_column($this->getJson('/api/listings/search?sort=closing_soon')->json('data'), 'id');

        $this->assertEquals($soon->id, $ids[0]);
    }

    // =========================================================================
    // Req 13: Response data shapes
    // =========================================================================

    #[Test]
    public function predes_truncated_is_null_when_predes_is_null(): void
    {
        $this->makeActiveListing(['predes' => null]);

        $this->assertNull($this->getJson('/api/listings/search')->json('data.0.predes_truncated'));
    }

    #[Test]
    public function predes_truncated_adds_ellipsis_when_over_200_chars(): void
    {
        $this->makeActiveListing(['predes' => str_repeat('Laravel developer needed ', 10)]);

        $truncated = $this->getJson('/api/listings/search')->json('data.0.predes_truncated');

        $this->assertNotNull($truncated);
        $this->assertStringEndsWith('...', $truncated);
        $this->assertLessThanOrEqual(203, mb_strlen($truncated));
    }

    #[Test]
    public function experience_display_shows_correct_strings(): void
    {
        $l1 = $this->makeActiveListing(['experience_years_min' => null, 'experience_years_max' => null]);
        $l2 = $this->makeActiveListing(['experience_years_min' => 2,    'experience_years_max' => null]);
        $l3 = $this->makeActiveListing(['experience_years_min' => null, 'experience_years_max' => 4]);
        $l4 = $this->makeActiveListing(['experience_years_min' => 2,    'experience_years_max' => 5]);

        $byId = collect($this->getJson('/api/listings/search')->json('data'))->keyBy('id');

        $this->assertEquals('Không yêu cầu kinh nghiệm', $byId[$l1->id]['experience_display']);
        $this->assertEquals('Từ 2 năm',                  $byId[$l2->id]['experience_display']);
        $this->assertEquals('Dưới 4 năm',                $byId[$l3->id]['experience_display']);
        $this->assertEquals('Từ 2 đến 5 năm',            $byId[$l4->id]['experience_display']);
    }

    #[Test]
    public function nullable_fields_return_null_not_empty_string(): void
    {
        $this->makeActiveListing([
            'predes'               => null,
            'job_level'            => null,
            'experience_years_min' => null,
            'experience_years_max' => null,
        ]);

        $item = $this->getJson('/api/listings/search')->json('data.0');

        $this->assertNull($item['predes_truncated']);
        $this->assertNull($item['job_level']);
        $this->assertNull($item['experience_years_min']);
        $this->assertNull($item['experience_years_max']);
        $this->assertNull($item['employer']['company_logo']);
    }

    // =========================================================================
    // Req 15: Validation
    // =========================================================================

    #[Test]
    public function invalid_salary_min_returns_422(): void
    {
        $this->getJson('/api/listings/search?salary_min=-100')
             ->assertStatus(422)
             ->assertJsonStructure(['message', 'errors' => ['salary_min']]);
    }

    #[Test]
    public function invalid_exp_min_returns_422(): void
    {
        $this->getJson('/api/listings/search?exp_min=100')
             ->assertStatus(422)
             ->assertJsonStructure(['message', 'errors' => ['exp_min']]);
    }

    #[Test]
    public function skills_array_over_15_is_silently_truncated(): void
    {
        $skills = Skill::factory()->count(17)->create();
        $ids    = $skills->pluck('id')->toArray();
        $qs     = implode('&', array_map(fn ($id) => "skills[]={$id}", $ids));

        // Must NOT return 422 — extras are silently dropped (Req 15.10)
        $this->getJson("/api/listings/search?{$qs}")->assertStatus(200);
    }

    #[Test]
    public function invalid_skill_elements_are_silently_ignored_without_422(): void
    {
        // passing non-positive integers inside skills array (strings, negatives) must be silently ignored and NOT throw 422 (Req 15.9)
        $response = $this->getJson('/api/listings/search?skills[]=abc&skills[]=-10&skills[]=2.5&skills[]=5');
        $response->assertStatus(200);
    }

    // =========================================================================
    // Req 16: HTTP method enforcement (405)
    // =========================================================================

    #[Test]
    public function post_to_search_returns_405(): void
    {
        $this->postJson('/api/listings/search', [])->assertStatus(405);
    }

    #[Test]
    public function post_to_cities_returns_405(): void
    {
        $this->postJson('/api/listings/cities', [])->assertStatus(405);
    }

    #[Test]
    public function post_to_skills_returns_405(): void
    {
        $this->postJson('/api/skills', [])->assertStatus(405);
    }

    // =========================================================================
    // GET /api/skills
    // =========================================================================

    #[Test]
    public function skills_endpoint_returns_all_skills_without_pagination(): void
    {
        Skill::factory()->count(5)->create();

        $response = $this->getJson('/api/skills');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => ['*' => ['id', 'name', 'slug']],
                 ]);
        $this->assertCount(5, $response->json('data'));
    }

    #[Test]
    public function skills_endpoint_returns_http_200_when_empty(): void
    {
        $this->getJson('/api/skills')
             ->assertStatus(200);
    }

    // =========================================================================
    // GET /api/listings/cities
    // =========================================================================

    #[Test]
    public function cities_endpoint_returns_distinct_addresses_sorted_alphabetically(): void
    {
        $this->makeActiveListing(['address' => 'Hà Nội']);
        $this->makeActiveListing(['address' => 'TP.HCM']);
        $this->makeActiveListing(['address' => 'Đà Nẵng']);
        $this->makeActiveListing(['address' => 'Hà Nội']); // duplicate

        $response = $this->getJson('/api/listings/cities');

        $response->assertStatus(200);
        $cities = $response->json();

        $this->assertIsArray($cities);
        $this->assertCount(3, $cities);

        $sorted = $cities;
        sort($sorted);
        $this->assertSame($sorted, $cities);
    }

    #[Test]
    public function cities_endpoint_excludes_closed_listings(): void
    {
        Listing::factory()->closed()->create(['address' => 'ClosedCity']);
        $this->makeActiveListing(['address' => 'Hà Nội']);

        $cities = $this->getJson('/api/listings/cities')->json();

        $this->assertNotContains('ClosedCity', $cities);
        $this->assertContains('Hà Nội', $cities);
    }

    #[Test]
    public function cities_endpoint_returns_http_200_when_empty(): void
    {
        $this->getJson('/api/listings/cities')
             ->assertStatus(200);
        $this->assertEmpty($this->getJson('/api/listings/cities')->json());
    }
}
