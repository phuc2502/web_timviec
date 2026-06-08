<?php

namespace Tests\Unit;

use App\Models\Listing;
use App\Models\Skill;
use App\Services\ListingSearchService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests for ListingSearchService.
 *
 * Uses RefreshDatabase so each test runs against a clean DB state.
 * Private methods are tested via ReflectionMethod.
 */
class ListingSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    private ListingSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ListingSearchService();
    }

    // -------------------------------------------------------------------------
    // applyKeywordFilter
    // -------------------------------------------------------------------------

    #[Test]
    public function keyword_filter_adds_relevance_score_to_select(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applyKeywordFilter');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, Listing::query(), 'Laravel developer');
        $sql    = $result->toSql();

        // Both MySQL (FULLTEXT) and SQLite (LIKE fallback) paths add relevance_score
        $this->assertStringContainsString('relevance_score', $sql);
    }

    #[Test]
    public function base_query_without_keyword_has_no_fulltext_clause(): void
    {
        $sql = Listing::active()->select('listings.*')->toSql();

        $this->assertStringNotContainsString('MATCH', $sql);
        $this->assertStringNotContainsString('relevance_score', $sql);
    }

    // -------------------------------------------------------------------------
    // applySkillFilter
    // -------------------------------------------------------------------------

    #[Test]
    public function skill_filter_and_mode_adds_subquery_with_having_count(): void
    {
        $skill1 = Skill::factory()->create(['name' => 'PHP',    'slug' => 'php']);
        $skill2 = Skill::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);

        $method = new \ReflectionMethod(ListingSearchService::class, 'applySkillFilter');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, Listing::query(), [$skill1->id, $skill2->id], 'and');
        $sql    = $result->toSql();

        $this->assertStringContainsString('listing_skill', $sql);
        $this->assertStringContainsString('HAVING', strtoupper($sql));
        $this->assertStringContainsString('COUNT', strtoupper($sql));
    }

    #[Test]
    public function skill_filter_or_mode_uses_exists_subquery(): void
    {
        $skill1 = Skill::factory()->create(['name' => 'React', 'slug' => 'react']);
        $skill2 = Skill::factory()->create(['name' => 'Vue',   'slug' => 'vue']);

        $method = new \ReflectionMethod(ListingSearchService::class, 'applySkillFilter');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, Listing::query(), [$skill1->id, $skill2->id], 'or');
        $sql    = $result->toSql();

        // whereHas generates an "exists (select * from listing_skill ...)" subquery
        $this->assertStringContainsString('exists', strtolower($sql));
        $this->assertStringContainsString('listing_skill', $sql);
    }

    #[Test]
    public function skill_filter_returns_unmodified_query_when_all_ids_invalid(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applySkillFilter');
        $method->setAccessible(true);

        $baseSql   = Listing::query()->toSql();
        $resultSql = $method->invoke($this->service, Listing::query(), [99999, 88888], 'and')->toSql();

        $this->assertSame($baseSql, $resultSql);
    }

    #[Test]
    public function skill_filter_ignores_invalid_ids_and_applies_filter_with_valid_ones(): void
    {
        $skill = Skill::factory()->create(['name' => 'Docker', 'slug' => 'docker']);

        $method = new \ReflectionMethod(ListingSearchService::class, 'applySkillFilter');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, Listing::query(), [$skill->id, 99999], 'and');

        $this->assertStringContainsString('listing_skill', $result->toSql());
    }

    // -------------------------------------------------------------------------
    // applySalaryFilter
    // -------------------------------------------------------------------------

    #[Test]
    public function salary_filter_not_activated_when_both_zero_or_null(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applySalaryFilter');
        $method->setAccessible(true);

        $baseSql = Listing::query()->toSql();

        foreach ([[null, null], [0, null], [null, 0], [0, 0]] as [$min, $max]) {
            $resultSql = $method->invoke($this->service, Listing::query(), $min, $max)->toSql();
            $this->assertSame($baseSql, $resultSql, "Failed for min={$min}, max={$max}");
        }
    }

    #[Test]
    public function salary_filter_min_only_adds_gte_and_gt_zero_conditions(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applySalaryFilter');
        $method->setAccessible(true);

        $result   = $method->invoke($this->service, Listing::query(), 5000000, null);
        $bindings = $result->getBindings();

        $this->assertContains(0, $bindings);
        $this->assertContains(5000000, $bindings);
    }

    #[Test]
    public function salary_filter_max_only_adds_lte_and_gt_zero_conditions(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applySalaryFilter');
        $method->setAccessible(true);

        $result   = $method->invoke($this->service, Listing::query(), null, 20000000);
        $bindings = $result->getBindings();

        $this->assertContains(0, $bindings);
        $this->assertContains(20000000, $bindings);
    }

    #[Test]
    public function salary_filter_applies_both_min_and_max(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applySalaryFilter');
        $method->setAccessible(true);

        $bindings = $method->invoke($this->service, Listing::query(), 5000000, 20000000)->getBindings();

        $this->assertContains(0, $bindings);
        $this->assertContains(5000000, $bindings);
        $this->assertContains(20000000, $bindings);
    }

    #[Test]
    public function salary_filter_zero_min_with_positive_max_applies_only_max_condition(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applySalaryFilter');
        $method->setAccessible(true);

        $result   = $method->invoke($this->service, Listing::query(), 0, 20000000);
        $bindings = $result->getBindings();

        // Only 2 bindings: salary > 0 and salary <= 20_000_000; NOT salary >= 0
        $this->assertContains(0, $bindings);
        $this->assertContains(20000000, $bindings);
        $this->assertSame(2, count($bindings));
    }

    // -------------------------------------------------------------------------
    // applyExperienceFilter
    // -------------------------------------------------------------------------

    #[Test]
    public function experience_filter_not_applied_when_both_null(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applyExperienceFilter');
        $method->setAccessible(true);

        $baseSql   = Listing::query()->toSql();
        $resultSql = $method->invoke($this->service, Listing::query(), null, null)->toSql();

        $this->assertSame($baseSql, $resultSql);
    }

    #[Test]
    public function experience_filter_min_only_adds_max_column_intersection_clause(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applyExperienceFilter');
        $method->setAccessible(true);

        $sql = $method->invoke($this->service, Listing::query(), 2, null)->toSql();

        $this->assertStringContainsString('experience_years_max', $sql);
        $this->assertStringContainsString('null', strtolower($sql));
    }

    #[Test]
    public function experience_filter_max_only_adds_min_column_intersection_clause(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applyExperienceFilter');
        $method->setAccessible(true);

        $sql = $method->invoke($this->service, Listing::query(), null, 5)->toSql();

        $this->assertStringContainsString('experience_years_min', $sql);
        $this->assertStringContainsString('null', strtolower($sql));
    }

    #[Test]
    public function experience_filter_both_adds_both_intersection_clauses(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applyExperienceFilter');
        $method->setAccessible(true);

        $sql = $method->invoke($this->service, Listing::query(), 2, 5)->toSql();

        $this->assertStringContainsString('experience_years_max', $sql);
        $this->assertStringContainsString('experience_years_min', $sql);
    }

    // -------------------------------------------------------------------------
    // applySort
    // -------------------------------------------------------------------------

    #[Test]
    public function sort_relevance_with_keyword_orders_by_relevance_score(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applySort');
        $method->setAccessible(true);

        $sql = $method->invoke($this->service, Listing::query(), 'relevance', true)->toSql();

        $this->assertStringContainsString('relevance_score', $sql);
    }

    #[Test]
    public function sort_relevance_without_keyword_falls_back_to_newest(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applySort');
        $method->setAccessible(true);

        $sql = $method->invoke($this->service, Listing::query(), 'relevance', false)->toSql();

        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringNotContainsString('relevance_score', $sql);
    }

    #[Test]
    public function sort_newest_orders_by_created_at_desc(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applySort');
        $method->setAccessible(true);

        $sql = $method->invoke($this->service, Listing::query(), 'newest', false)->toSql();

        $this->assertStringContainsString('created_at', $sql);
    }

    #[Test]
    public function sort_salary_desc_uses_case_expression(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applySort');
        $method->setAccessible(true);

        $sql = $method->invoke($this->service, Listing::query(), 'salary_desc', false)->toSql();

        $this->assertStringContainsString('CASE WHEN salary = 0', $sql);
        $this->assertStringContainsString('salary DESC', $sql);
    }

    #[Test]
    public function sort_salary_asc_uses_case_expression(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applySort');
        $method->setAccessible(true);

        $sql = $method->invoke($this->service, Listing::query(), 'salary_asc', false)->toSql();

        $this->assertStringContainsString('CASE WHEN salary = 0', $sql);
        $this->assertStringContainsString('salary ASC', $sql);
    }

    #[Test]
    public function sort_closing_soon_orders_by_application_close_date(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applySort');
        $method->setAccessible(true);

        $sql = $method->invoke($this->service, Listing::query(), 'closing_soon', false)->toSql();

        $this->assertStringContainsString('application_close_date', $sql);
    }

    #[Test]
    public function sort_invalid_value_falls_back_to_newest(): void
    {
        $method = new \ReflectionMethod(ListingSearchService::class, 'applySort');
        $method->setAccessible(true);

        $sql = $method->invoke($this->service, Listing::query(), 'invalid_sort', false)->toSql();

        $this->assertStringContainsString('created_at', $sql);
    }

    // -------------------------------------------------------------------------
    // getCities
    // -------------------------------------------------------------------------

    #[Test]
    public function get_cities_returns_sorted_array_of_strings(): void
    {
        $cities = $this->service->getCities();

        $this->assertIsArray($cities);

        if (count($cities) > 1) {
            $sorted = $cities;
            sort($sorted);
            $this->assertSame($sorted, $cities, 'Cities should be sorted alphabetically');
        }
    }
}
