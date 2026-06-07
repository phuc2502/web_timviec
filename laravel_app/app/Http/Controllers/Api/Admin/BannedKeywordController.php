<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannedKeyword;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BannedKeywordController extends Controller
{
    /**
     * Danh sách từ khóa cấm.
     */
    public function index(Request $request): JsonResponse
    {
        $query = BannedKeyword::orderByDesc('created_at');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        $keywords = $query->paginate(15);
        return response()->json($keywords);
    }

    /**
     * Thêm từ khóa cấm mới.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'keyword' => 'required|string|max:100|unique:banned_keywords,keyword',
            'is_active' => 'boolean',
            'severity' => 'nullable|string|in:high,medium,low',
        ], [
            'keyword.required' => 'Từ khóa là bắt buộc.',
            'keyword.unique' => 'Từ khóa này đã tồn tại.',
            'keyword.max' => 'Từ khóa không được dài quá 100 ký tự.',
            'severity.in' => 'Mức độ nghiêm trọng phải là high, medium, hoặc low.',
        ]);

        $keyword = BannedKeyword::create([
            'keyword' => trim($request->keyword),
            'is_active' => $request->input('is_active', true),
            'severity' => $request->input('severity', 'high'),
        ]);

        // Clear banned keywords cache
        Cache::forget('banned_keywords');

        return response()->json($keyword, 201);
    }

    /**
     * Cập nhật từ khóa cấm.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $keywordModel = BannedKeyword::findOrFail($id);

        $request->validate([
            'keyword' => 'required|string|max:100|unique:banned_keywords,keyword,' . $keywordModel->id,
            'is_active' => 'boolean',
            'severity' => 'nullable|string|in:high,medium,low',
        ], [
            'keyword.required' => 'Từ khóa là bắt buộc.',
            'keyword.unique' => 'Từ khóa này đã tồn tại.',
            'keyword.max' => 'Từ khóa không được dài quá 100 ký tự.',
            'severity.in' => 'Mức độ nghiêm trọng phải là high, medium, hoặc low.',
        ]);

        $keywordModel->update([
            'keyword' => trim($request->keyword),
            'is_active' => $request->input('is_active', $keywordModel->is_active),
            'severity' => $request->input('severity', $keywordModel->severity),
        ]);

        // Clear cache
        Cache::forget('banned_keywords');

        return response()->json($keywordModel);
    }

    /**
     * Xóa từ khóa cấm.
     */
    public function destroy(int $id): JsonResponse
    {
        $keywordModel = BannedKeyword::findOrFail($id);
        $keywordModel->delete();

        // Clear cache
        Cache::forget('banned_keywords');

        return response()->json([
            'message' => 'Đã xóa từ khóa cấm thành công.'
        ]);
    }
}
