<?php

namespace Tests\Feature;

use App\Models\BannedKeyword;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Models\ListingReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class JobPostingTest extends TestCase
{
    use RefreshDatabase;

    private User $employer;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test employer user
        $this->employer = User::factory()->create([
            'user_type' => 'employer',
            'status' => 'trial',
            'user_trial' => now()->addDays(30),
        ]);

        // Create a default category
        $this->category = Category::create([
            'name' => 'Công nghệ thông tin',
            'slug' => 'cong-nghe-thong-tin',
            'is_active' => true,
        ]);
        
        Cache::flush();
    }

    /**
     * Test auto-moderation rejects listings containing banned keywords.
     */
    public function test_auto_moderation_rejects_banned_keywords(): void
    {
        // Add a banned keyword
        BannedKeyword::create([
            'keyword' => 'lừa đảo',
            'is_active' => true,
            'severity' => 'high',
        ]);

        $response = $this->actingAs($this->employer)->postJson('/api/employer/listings', [
            'title' => 'Tuyển CTV việc nhẹ lương cao lừa đảo',
            'description' => 'Mô tả công việc bình thường.',
            'category_id' => $this->category->id,
            'job_type' => 'part_time',
            'address' => 'Hà Nội',
            'is_negotiable' => true,
            'application_close_date' => now()->addDays(10)->toDateString(),
            'publish_mode' => 'immediate',
        ]);

        $response->assertStatus(201);
        $listingId = $response->json('id');

        $this->assertDatabaseHas('listings', [
            'id' => $listingId,
            'status' => 'rejected',
        ]);
    }

    /**
     * Test quota system blocks job creation when trial limit is exceeded.
     */
    public function test_quota_limit_blocks_creation(): void
    {
        // Create 5 active listings for this employer (max limit for trial)
        Listing::factory()->count(5)->create([
            'user_id' => $this->employer->id,
            'category_id' => $this->category->id,
            'status' => 'active',
            'application_close_date' => now()->addDays(10)->toDateString(),
        ]);

        // Try to create the 6th listing
        $response = $this->actingAs($this->employer)->postJson('/api/employer/listings', [
            'title' => 'Vị trí thứ 6',
            'description' => 'Mô tả công việc.',
            'category_id' => $this->category->id,
            'job_type' => 'full_time',
            'address' => 'Hà Nội',
            'is_negotiable' => true,
            'application_close_date' => now()->addDays(10)->toDateString(),
            'publish_mode' => 'immediate',
        ]);

        // Should return 403 Forbidden because quota is exceeded
        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Bạn đã đạt giới hạn tin đăng. Vui lòng nâng cấp gói hoặc đóng bớt tin cũ.');
    }

    /**
     * Test rate limiting blocks creation of more than 2 listings in 24 hours.
     */
    public function test_rate_limit_blocks_creation(): void
    {
        // 1st listing (Allowed)
        $this->actingAs($this->employer)->postJson('/api/employer/listings', [
            'title' => 'Tin thứ nhất',
            'description' => 'Mô tả.',
            'category_id' => $this->category->id,
            'job_type' => 'full_time',
            'address' => 'Hà Nội',
            'is_negotiable' => true,
            'application_close_date' => now()->addDays(10)->toDateString(),
            'publish_mode' => 'immediate',
        ])->assertStatus(201);

        // 2nd listing (Allowed)
        $this->actingAs($this->employer)->postJson('/api/employer/listings', [
            'title' => 'Tin thứ hai',
            'description' => 'Mô tả.',
            'category_id' => $this->category->id,
            'job_type' => 'full_time',
            'address' => 'Hà Nội',
            'is_negotiable' => true,
            'application_close_date' => now()->addDays(10)->toDateString(),
            'publish_mode' => 'immediate',
        ])->assertStatus(201);

        // 3rd listing (Blocked by Rate Limit)
        $response = $this->actingAs($this->employer)->postJson('/api/employer/listings', [
            'title' => 'Tin thứ ba',
            'description' => 'Mô tả.',
            'category_id' => $this->category->id,
            'job_type' => 'full_time',
            'address' => 'Hà Nội',
            'is_negotiable' => true,
            'application_close_date' => now()->addDays(10)->toDateString(),
            'publish_mode' => 'immediate',
        ]);

        // Should return 429 Too Many Requests
        $response->assertStatus(429);
        $response->assertJsonPath('message', 'Bạn đã vượt quá giới hạn tạo tin trong 24 giờ.');
    }

    /**
     * Test violation report auto-pauses job listing when 5+ pending reports are submitted.
     */
    public function test_report_auto_pauses_listing(): void
    {
        // Create an active listing
        $listing = Listing::factory()->create([
            'user_id' => $this->employer->id,
            'category_id' => $this->category->id,
            'status' => 'active',
            'application_close_date' => now()->addDays(10)->toDateString(),
        ]);

        // Create 5 different candidate users to submit reports
        $candidates = User::factory()->count(5)->create(['user_type' => 'candidate']);

        // Submit 4 reports
        for ($i = 0; $i < 4; $i++) {
            $this->actingAs($candidates[$i])->postJson("/api/listings/{$listing->id}/report", [
                'reason' => 'scam',
                'description' => 'Nghi ngờ lừa đảo.',
            ])->assertStatus(201);
        }

        // Listing should still be active
        $this->assertEquals('active', $listing->fresh()->status);

        // Submit the 5th report
        $this->actingAs($candidates[4])->postJson("/api/listings/{$listing->id}/report", [
            'reason' => 'scam',
            'description' => 'Nghi ngờ lừa đảo lần nữa.',
        ])->assertStatus(201);

        // Listing should be auto-paused now
        $this->assertEquals('paused', $listing->fresh()->status);
    }
}
