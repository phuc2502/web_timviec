<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Danh sách danh mục.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::orderBy('name');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->boolean('all')) {
            return response()->json($query->get());
        }

        $categories = $query->paginate(15);
        return response()->json($categories);
    }

    /**
     * Tạo danh mục mới.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Tên danh mục là bắt buộc.',
            'name.unique' => 'Danh mục này đã tồn tại.',
            'name.max' => 'Tên danh mục không vượt quá 100 ký tự.',
        ]);

        $name = trim($request->name);
        $slug = Str::slug($name);

        // Ensure slug is unique
        $originalSlug = $slug;
        $count = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $category = Category::create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => $request->input('is_active', true),
        ]);

        return response()->json($category, 201);
    }

    /**
     * Cập nhật danh mục.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Tên danh mục là bắt buộc.',
            'name.unique' => 'Danh mục này đã tồn tại.',
            'name.max' => 'Tên danh mục không vượt quá 100 ký tự.',
        ]);

        $name = trim($request->name);
        $data = [
            'name' => $name,
            'is_active' => $request->input('is_active', $category->is_active),
        ];

        // Only update slug if name changed
        if ($category->name !== $name) {
            $slug = Str::slug($name);
            $originalSlug = $slug;
            $count = 1;
            while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            $data['slug'] = $slug;
        }

        $category->update($data);

        return response()->json($category);
    }

    /**
     * Xóa danh mục (chỉ khi chưa có tin tuyển dụng nào sử dụng).
     */
    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        if ($category->listings()->exists()) {
            return response()->json([
                'message' => 'Không thể xóa danh mục này vì đang có tin tuyển dụng sử dụng nó.'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Đã xóa danh mục thành công.'
        ]);
    }
}
