<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Mockery;
use Tests\TestCase;

class CvAiParserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Cấu hình API key giả lập cho các test
        config(['services.gemini.key' => 'test-gemini-key']);
        Storage::fake('local');

        // Tạo các bảng thủ công để tránh lỗi sắp xếp migration của hệ thống
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('user_type')->default('employee');
            $table->string('resume')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('cv_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('full_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->text('objective')->nullable();
            $table->json('education')->nullable();
            $table->json('experience')->nullable();
            $table->json('projects')->nullable();
            $table->json('certifications')->nullable();
            $table->text('skills_text')->nullable();
            $table->json('languages')->nullable();
            $table->string('template')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('cv_data');
        Schema::dropIfExists('users');
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test trích xuất CV thành công.
     */
    public function test_ai_parse_cv_success()
    {
        $user = User::factory()->create(['user_type' => 'employee']);
        
        // Mock PDF Parser
        $mockPdf = Mockery::mock(\Smalot\PdfParser\Document::class);
        $mockPdf->shouldReceive('getText')->andReturn(str_repeat('This is a very long text to satisfy the minimum length requirement of 100 characters in the PDF content extractor logic.', 3));

        $mockParser = Mockery::mock(\Smalot\PdfParser\Parser::class);
        $mockParser->shouldReceive('parseFile')->andReturn($mockPdf);
        $this->app->instance(\Smalot\PdfParser\Parser::class, $mockParser);

        // Mock Gemini API Response
        $mockedData = [
            'full_name' => 'Nguyễn Văn A',
            'email' => 'nguyenvana@gmail.com',
            'phone' => '0901234567',
            'address' => 'Hà Nội',
            'objective' => 'Học hỏi và cống hiến',
            'skills_text' => 'PHP, Laravel, VueJS',
            'education' => [
                ['school' => 'Đại học Bách Khoa', 'degree' => 'Kỹ sư CNTT', 'year_start' => '2018', 'year_end' => '2022']
            ],
            'experience' => [],
            'projects' => [],
            'certifications' => [],
            'languages' => []
        ];

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($mockedData)]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Upload file PDF giả lập
        $file = UploadedFile::fake()->createWithContent('resume.pdf', "%PDF-1.4\nDemo content");

        $response = $this->actingAs($user)
            ->postJson(route('user.cv.ai-parse'), [
                'cv_file' => $file
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockedData
            ]);

        // Kiểm tra file tạm đã bị xóa khỏi Storage local
        $this->assertEmpty(Storage::disk('local')->allFiles('temp_cvs'));
    }

    /**
     * Test file PDF giả mạo (MIME type bypass attempt).
     */
    public function test_ai_parse_cv_fake_pdf()
    {
        $user = User::factory()->create(['user_type' => 'employee']);

        // Upload file có đuôi .pdf nhưng nội dung không chứa %PDF ở 4 bytes đầu
        $file = UploadedFile::fake()->createWithContent('fake.pdf', "NOT_A_PDF_content");

        $response = $this->actingAs($user)
            ->postJson(route('user.cv.ai-parse'), [
                'cv_file' => $file
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => 'File PDF không hợp lệ (Sai định dạng PDF thực tế).'
            ]);

        // Kiểm tra file tạm đã bị xóa (hoặc không được lưu)
        $this->assertEmpty(Storage::disk('local')->allFiles('temp_cvs'));
    }

    /**
     * Test file PDF quét ảnh/scan (Không có văn bản).
     */
    public function test_ai_parse_cv_scanned_pdf()
    {
        $user = User::factory()->create(['user_type' => 'employee']);

        // Mock PDF Parser trả về text quá ngắn (< 100 ký tự)
        $mockPdf = Mockery::mock(\Smalot\PdfParser\Document::class);
        $mockPdf->shouldReceive('getText')->andReturn('Short text.');

        $mockParser = Mockery::mock(\Smalot\PdfParser\Parser::class);
        $mockParser->shouldReceive('parseFile')->andReturn($mockPdf);
        $this->app->instance(\Smalot\PdfParser\Parser::class, $mockParser);

        $file = UploadedFile::fake()->createWithContent('scanned.pdf', "%PDF-1.4\nDemo content");

        $response = $this->actingAs($user)
            ->postJson(route('user.cv.ai-parse'), [
                'cv_file' => $file
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => 'File PDF không chứa văn bản có thể sao chép (có thể là ảnh quét). Vui lòng tải lên file PDF định dạng text.'
            ]);

        $this->assertEmpty(Storage::disk('local')->allFiles('temp_cvs'));
    }

    /**
     * Test Quota Limit (429) của Gemini API.
     */
    public function test_ai_parse_cv_quota_limit()
    {
        $user = User::factory()->create(['user_type' => 'employee']);

        $mockPdf = Mockery::mock(\Smalot\PdfParser\Document::class);
        $mockPdf->shouldReceive('getText')->andReturn(str_repeat('Long text sample for testing quota limits on Gemini API call.', 5));

        $mockParser = Mockery::mock(\Smalot\PdfParser\Parser::class);
        $mockParser->shouldReceive('parseFile')->andReturn($mockPdf);
        $this->app->instance(\Smalot\PdfParser\Parser::class, $mockParser);

        // Mock Gemini API trả về 429
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'Rate limit exceeded']
            ], 429)
        ]);

        $file = UploadedFile::fake()->createWithContent('resume.pdf', "%PDF-1.4\nDemo content");

        $response = $this->actingAs($user)
            ->postJson(route('user.cv.ai-parse'), [
                'cv_file' => $file
            ]);

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'error' => 'Hệ thống AI đang quá tải (vượt giới hạn lượt gọi). Vui lòng thử lại sau ít phút.'
            ]);

        $this->assertEmpty(Storage::disk('local')->allFiles('temp_cvs'));
    }

    /**
     * Test kết nối bị timeout khi gọi Gemini API.
     */
    public function test_ai_parse_cv_connection_timeout()
    {
        $user = User::factory()->create(['user_type' => 'employee']);

        $mockPdf = Mockery::mock(\Smalot\PdfParser\Document::class);
        $mockPdf->shouldReceive('getText')->andReturn(str_repeat('Long text sample for testing connection timeout limits on Gemini API call.', 5));

        $mockParser = Mockery::mock(\Smalot\PdfParser\Parser::class);
        $mockParser->shouldReceive('parseFile')->andReturn($mockPdf);
        $this->app->instance(\Smalot\PdfParser\Parser::class, $mockParser);

        // Mock Gemini API ném Exception Timeout
        Http::fake([
            'generativelanguage.googleapis.com/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
            }
        ]);

        $file = UploadedFile::fake()->createWithContent('resume.pdf', "%PDF-1.4\nDemo content");

        $response = $this->actingAs($user)
            ->postJson(route('user.cv.ai-parse'), [
                'cv_file' => $file
            ]);

        $response->assertStatus(504)
            ->assertJson([
                'success' => false,
                'error' => 'Kết nối tới máy chủ AI bị quá thời gian phản hồi. Vui lòng thử lại.'
            ]);

        $this->assertEmpty(Storage::disk('local')->allFiles('temp_cvs'));
    }
}
