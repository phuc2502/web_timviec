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
}
