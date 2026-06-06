<?php

namespace App\Http\Controllers;

use App\Http\Requests\CvFormRequest;
use App\Http\Requests\UploadCvRequest;
use App\Models\CvData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UserController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════════
    // 1. CV UPLOAD
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * GET user/cv — Trang quản lý CV (upload + summary).
     */
    public function cv()
    {
        $user   = auth()->user();
        $cvData = CvData::where('user_id', $user->id)->first();

        return view('user.cv', compact('user', 'cvData'));
    }

    /**
     * POST user/cv — Upload file CV.
     * - Xóa file cũ nếu tồn tại.
     * - Lưu file mới với UUID prefix.
     */
    public function updateCv(UploadCvRequest $request)
    {
        $user = auth()->user();

        try {
            // Xóa file cũ khỏi Storage nếu có
            if ($user->resume && Storage::disk('public')->exists($user->resume)) {
                Storage::disk('public')->delete($user->resume);
            }

            // Lưu file mới
            $file     = $request->file('cv_file');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs('resume', $filename, 'public');

            // Cập nhật DB
            $user->resume = $path;
            $user->save();

            return redirect()->route('user.cv')->with('success', 'Tải lên CV thành công!');
        } catch (\Exception $e) {
            Log::error('CV upload failed: ' . $e->getMessage());
            return back()->with('error', 'Không thể lưu file CV. Vui lòng thử lại.');
        }
    }

    /**
     * GET user/cv/view — Serve file CV inline.
     * Lấy trực tiếp từ auth()->user()->resume, không nhận tham số từ request (IDOR-safe).
     */
    public function viewCv()
    {
        $user = auth()->user();

        if (!$user->resume) {
            return redirect()->route('user.cv')->with('info', 'Bạn chưa tải lên CV nào.');
        }

        if (!Storage::disk('public')->exists($user->resume)) {
            return redirect()->route('user.cv')->with('error', 'File CV không tồn tại trên hệ thống. Vui lòng tải lên lại.');
        }

        return response()->file(Storage::disk('public')->path($user->resume));
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 2. CV ONLINE
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * GET user/cv/create — Form tạo/chỉnh sửa CV online.
     * Pre-populate từ cv_data nếu đã tồn tại.
     */
    public function createCv()
    {
        $cvData    = CvData::where('user_id', auth()->id())->first();
        $templates = config('cv.templates');

        return view('user.create-cv', compact('cvData', 'templates'));
    }

    /**
     * POST user/cv/preview — Lưu (upsert) dữ liệu CV + ảnh, rồi redirect tới preview.
     *
     * Sử dụng DB::transaction để đảm bảo tính toàn vẹn:
     * - Nếu lưu DB lỗi → tự dọn dẹp ảnh mới đã upload.
     * - Nếu có ảnh cũ và user upload ảnh mới → xóa ảnh cũ khỏi Storage.
     */
    public function saveCv(CvFormRequest $request)
    {
        $user      = auth()->user();
        $cvData    = CvData::where('user_id', $user->id)->first();
        $newPhotoPath = null;

        try {
            // Xử lý ảnh đại diện
            if ($request->hasFile('photo')) {
                // Xóa ảnh cũ nếu có
                if ($cvData && $cvData->photo_path && Storage::disk('public')->exists($cvData->photo_path)) {
                    Storage::disk('public')->delete($cvData->photo_path);
                }

                $photo    = $request->file('photo');
                $filename = Str::uuid() . '.' . $photo->getClientOriginalExtension();
                $newPhotoPath = $photo->storeAs('images/cv', $filename, 'public');
            }

            DB::transaction(function () use ($request, $user, $cvData, $newPhotoPath) {
                $data = $request->only([
                    'full_name', 'phone', 'email', 'address', 'objective',
                    'education', 'experience', 'projects', 'certifications',
                    'skills_text', 'languages', 'template',
                ]);

                if ($newPhotoPath) {
                    $data['photo_path'] = $newPhotoPath;
                }

                CvData::updateOrCreate(
                    ['user_id' => $user->id],
                    $data
                );
            });

            return redirect()->route('user.cv.preview')->with('success', 'Thông tin CV đã được lưu thành công!');

        } catch (\Exception $e) {
            // Dọn dẹp ảnh mới nếu đã upload nhưng lưu DB lỗi (orphan cleanup)
            if ($newPhotoPath && Storage::disk('public')->exists($newPhotoPath)) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            Log::error('CV save failed: ' . $e->getMessage());

            return back()
                ->with('error', 'Không thể lưu CV. Vui lòng thử lại.')
                ->withInput();
        }
    }

    /**
     * GET user/cv/preview — Render preview HTML từ cv_data trong DB.
     * Kiểm tra template tồn tại, log warning + fallback về default nếu không có.
     */
    public function showPreview()
    {
        $cvData = CvData::where('user_id', auth()->id())->first();

        if (!$cvData) {
            return redirect()->route('user.cv.create')->with('info', 'Vui lòng điền thông tin CV trước.');
        }

        $template = $cvData->template ?? 'default';

        // Kiểm tra template tồn tại
        if (!view()->exists("cv-templates.{$template}")) {
            Log::warning("CV template '{$template}' not found for user " . auth()->id() . ", falling back to default.");
            $template = 'default';
            session()->flash('warning', "Template \"{$cvData->template}\" không khả dụng. Đã sử dụng template mặc định.");
        }

        // Chuẩn bị photo URL
        $photoUrl = null;
        if ($cvData->photo_path && Storage::disk('public')->exists($cvData->photo_path)) {
            $photoUrl = asset('storage/' . $cvData->photo_path);
        }

        return view('user.cv-preview', [
            'cvData'       => $cvData,
            'template'     => $template,
            'photoUrl'     => $photoUrl,
            'templateView' => "cv-templates.{$template}",
        ]);
    }

    /**
     * GET user/cv/download — Xuất PDF từ cv_data.
     * - set_time_limit(60) để tránh timeout với ảnh base64 nặng.
     * - Ownership: chỉ lấy cv_data từ auth()->id(), không nhận tham số.
     * - Ảnh đại diện → mã hóa base64 để nhúng vào PDF.
     */
    public function downloadPdf()
    {
        set_time_limit(60);

        $cvData = CvData::where('user_id', auth()->id())->first();

        if (!$cvData) {
            return redirect()->route('user.cv.create')->with('info', 'Vui lòng tạo CV online trước khi tải PDF.');
        }

        $template = $cvData->template ?? 'default';

        if (!view()->exists("cv-templates.{$template}")) {
            Log::warning("CV template '{$template}' not found for PDF generation, user " . auth()->id() . ", falling back to default.");
            $template = 'default';
        }

        // Base64-encode ảnh đại diện
        $photoBase64 = null;
        if ($cvData->photo_path && Storage::disk('public')->exists($cvData->photo_path)) {
            $photoContent  = Storage::disk('public')->get($cvData->photo_path);
            $photoMime     = Storage::disk('public')->mimeType($cvData->photo_path);
            $photoBase64   = 'data:' . $photoMime . ';base64,' . base64_encode($photoContent);
        }

        try {
            $pdf = Pdf::loadView("cv-templates.{$template}", [
                'cvData'       => $cvData,
                'photoBase64'  => $photoBase64,
                'isPdf'        => true,
            ])->setPaper('a4', 'portrait');

            return $pdf->download('cv-' . auth()->id() . '.pdf');

        } catch (\Exception $e) {
            Log::error('PDF generation failed: ' . $e->getMessage());
            return redirect()->route('user.cv.preview')->with('error', 'Không thể tạo PDF. Vui lòng thử lại.');
        }
    }

    /**
     * DELETE user/cv/online — Xóa CV online.
     * Xóa bản ghi cv_data + dọn dẹp ảnh trên Storage.
     */
    public function deleteOnlineCv()
    {
        $cvData = CvData::where('user_id', auth()->id())->first();

        if (!$cvData) {
            return redirect()->route('user.cv.create')->with('info', 'Không tìm thấy CV online để xóa.');
        }

        // Dọn dẹp ảnh đại diện
        if ($cvData->photo_path) {
            try {
                Storage::disk('public')->delete($cvData->photo_path);
            } catch (\Exception $e) {
                Log::error('CV photo cleanup failed: ' . $e->getMessage());
                // Vẫn xóa bản ghi dù xóa file thất bại (theo spec)
            }
        }

        $cvData->delete();

        return redirect()->route('user.cv.create')->with('success', 'CV online đã được xóa thành công.');
    }

    /**
     * Trích xuất thông tin CV bằng AI.
     */
    public function aiParseCv(Request $request)
    {
        // 1. Xác thực file cơ bản qua Laravel
        $request->validate([
            'cv_file' => 'required|file|mimes:pdf|max:5120',
        ], [
            'cv_file.required' => 'Vui lòng chọn một file PDF.',
            'cv_file.file' => 'Tập tin tải lên không hợp lệ.',
            'cv_file.mimes' => 'Hệ thống chỉ hỗ trợ file định dạng PDF.',
            'cv_file.max' => 'Dung lượng file tối đa là 5MB.',
        ]);

        $file = $request->file('cv_file');

        // 2. Xác thực magic bytes (kiểm tra signature %PDF) để tránh giả mạo extension
        $handle = fopen($file->getPathname(), 'r');
        $header = fread($handle, 4);
        fclose($handle);

        if ($header !== '%PDF') {
            return response()->json([
                'success' => false,
                'error' => 'File PDF không hợp lệ (Sai định dạng PDF thực tế).'
            ], 422);
        }

        // 3. Lưu tạm file vào Storage local bảo mật
        $tempPath = $file->store('temp_cvs', 'local');

        try {
            $realPath = Storage::disk('local')->path($tempPath);

            // 4. Trích xuất text từ PDF
            $parser = app(\Smalot\PdfParser\Parser::class);
            $pdf = $parser->parseFile($realPath);
            $rawText = $pdf->getText();

            // Kiểm tra độ dài văn bản trích xuất (tránh PDF quét ảnh không chứa text layer)
            if (mb_strlen(trim($rawText)) < 100) {
                return response()->json([
                    'success' => false,
                    'error' => 'File PDF không chứa văn bản có thể sao chép (có thể là ảnh quét). Vui lòng tải lên file PDF định dạng text.'
                ], 422);
            }

            // Giới hạn độ dài văn bản gửi đi tránh token limit
            $rawText = Str::limit($rawText, 15000, '...');

            // 5. Gọi Gemini API
            $data = $this->callGeminiApi($rawText);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('CV AI Parser Exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            // Trả về mã lỗi phù hợp (ví dụ 429 hoặc 503 cho quota limit)
            $statusCode = 500;
            $errorMsg = 'Có lỗi xảy ra trong quá trình xử lý CV bằng AI. Vui lòng thử lại sau.';
            
            if ($e->getCode() === 429 || $e->getMessage() === 'Gemini API quota exceeded') {
                $statusCode = 503;
                $errorMsg = 'Hệ thống AI đang quá tải (vượt giới hạn lượt gọi). Vui lòng thử lại sau ít phút.';
            } else if (str_contains(strtolower($e->getMessage()), 'timeout') || str_contains(strtolower($e->getMessage()), 'connect')) {
                $statusCode = 504;
                $errorMsg = 'Kết nối tới máy chủ AI bị quá thời gian phản hồi. Vui lòng thử lại.';
            }

            return response()->json([
                'success' => false,
                'error' => $errorMsg
            ], $statusCode);

        } finally {
            // 6. Xóa file tạm ngay lập tức (bảo vệ quyền riêng tư)
            if (Storage::disk('local')->exists($tempPath)) {
                Storage::disk('local')->delete($tempPath);
            }
        }
    }

    /**
     * Gọi Gemini API để trích xuất thông tin CV từ văn bản thô.
     */
    private function callGeminiApi(string $text): array
    {
        $apiKey = trim(config('services.gemini.key'));
        $model = config('services.gemini.model', 'gemini-2.5-flash');

        if (!$apiKey) {
            throw new \Exception('GEMINI_API_KEY chưa được cấu hình.');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;

        $prompt = "Bạn là một AI chuyên nghiệp trong việc đọc và trích xuất thông tin từ CV tiếng Việt hoặc tiếng Anh.\n" .
            "Nhiệm vụ của bạn là đọc nội dung văn bản CV thô dưới đây và phân loại thông tin vào cấu trúc dữ liệu JSON được yêu cầu.\n" .
            "Hãy cố gắng điền chính xác nhất có thể. Nếu không tìm thấy thông tin cho một trường nào đó, hãy trả về chuỗi rỗng hoặc mảng rỗng tương ứng.\n" .
            "Đối với các danh sách (như học vấn, kinh nghiệm, dự án, chứng chỉ, ngoại ngữ), nếu CV không có thông tin thì trả về mảng rỗng [].\n\n" .
            "Nội dung văn bản CV thô:\n" . $text;

        // Định nghĩa Schema theo chuẩn Gemini Structured Outputs
        $schema = [
            'type' => 'OBJECT',
            'properties' => [
                'full_name' => ['type' => 'STRING'],
                'email' => ['type' => 'STRING'],
                'phone' => ['type' => 'STRING'],
                'address' => ['type' => 'STRING'],
                'objective' => ['type' => 'STRING'],
                'skills_text' => ['type' => 'STRING'],
                'education' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'school' => ['type' => 'STRING'],
                            'degree' => ['type' => 'STRING'],
                            'year_start' => ['type' => 'STRING'],
                            'year_end' => ['type' => 'STRING']
                        ],
                        'required' => ['school']
                    ]
                ],
                'experience' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'company' => ['type' => 'STRING'],
                            'role' => ['type' => 'STRING'],
                            'year_start' => ['type' => 'STRING'],
                            'year_end' => ['type' => 'STRING'],
                            'desc' => ['type' => 'STRING']
                        ],
                        'required' => ['company']
                    ]
                ],
                'projects' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'name' => ['type' => 'STRING'],
                            'tech' => ['type' => 'STRING'],
                            'url' => ['type' => 'STRING'],
                            'desc' => ['type' => 'STRING']
                        ],
                        'required' => ['name']
                    ]
                ],
                'certifications' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'name' => ['type' => 'STRING'],
                            'issuer' => ['type' => 'STRING'],
                            'year' => ['type' => 'STRING']
                        ],
                        'required' => ['name']
                    ]
                ],
                'languages' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'lang' => ['type' => 'STRING'],
                            'level' => ['type' => 'STRING']
                        ],
                        'required' => ['lang']
                    ]
                ]
            ],
            'required' => ['full_name', 'email']
        ];

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => $schema
            ]
        ];

        $response = Http::timeout(30)
            ->retry(2, 500, function ($exception, $request) {
                if ($exception instanceof \Illuminate\Http\Client\ConnectionException) {
                    return true;
                }
                if ($exception instanceof \Illuminate\Http\Client\RequestException && $exception->response->status() >= 500) {
                    return true;
                }
                return false;
            })
            ->post($url, $payload);

        if ($response->failed()) {
            $status = $response->status();
            $body = $response->body();
            Log::error("Gemini API call failed status {$status}: {$body}");

            if ($status === 429) {
                throw new \Exception('Gemini API quota exceeded', 429);
            }
            throw new \Exception("Gemini API returned status {$status}");
        }

        $result = $response->json();
        $candidates = $result['candidates'] ?? [];
        if (empty($candidates)) {
            throw new \Exception('Gemini API did not return candidates');
        }

        $outputText = $candidates[0]['content']['parts'][0]['text'] ?? '';
        if (empty($outputText)) {
            throw new \Exception('Gemini API returned empty text');
        }

        $data = json_decode($outputText, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to decode JSON response from Gemini');
        }

        return $data;
    }
}
