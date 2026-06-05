<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SkillController extends Controller
{
    /**
     * Tìm kiếm autocomplete kỹ năng.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $limit = min((int)$request->input('limit', 10), 50);

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $skills = Skill::where('name', 'like', '%' . $query . '%')
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->get();

        return response()->json($skills);
    }

    /**
     * Tạo kỹ năng mới.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:skills,name',
        ], [
            'name.required' => 'Tên kỹ năng là bắt buộc.',
            'name.unique' => 'Kỹ năng này đã tồn tại.',
            'name.max' => 'Tên kỹ năng không được dài quá 100 ký tự.',
        ]);

        $name = trim($request->name);
        $slug = Str::slug($name);

        // Double check slug uniqueness
        $originalSlug = $slug;
        $count = 1;
        while (Skill::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $skill = Skill::create([
            'name' => $name,
            'slug' => $slug,
            'usage_count' => 0
        ]);

        return response()->json($skill, 201);
    }
}
