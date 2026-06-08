<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'title'                  => $this->title,
            'slug'                   => $this->slug,

            // Req 13.1: predes cắt tại ranh giới từ ≤ 200 ký tự; null nếu gốc là null
            'predes_truncated'       => $this->truncatePredes($this->predes, 200),

            'job_type'               => $this->job_type,
            'work_mode'              => $this->work_mode,
            'job_level'              => $this->job_level,        // nullable → null (không phải "")
            'address'                => $this->address,

            'salary'                 => $this->salary,
            'salary_display'         => $this->formatSalary($this->salary),

            'experience_years_min'   => $this->experience_years_min,  // nullable
            'experience_years_max'   => $this->experience_years_max,  // nullable
            'experience_display'     => $this->formatExperience(
                $this->experience_years_min,
                $this->experience_years_max
            ),

            'application_close_date' => $this->application_close_date,
            'created_at'             => $this->created_at,

            // Req 13.2: employer info
            'employer' => [
                'company_name' => $this->employer->company_name,
                'company_logo' => $this->employer->company_logo,  // nullable
                'company_size' => $this->employer->company_size,
            ],

            // Req 13.3: skills — [] if none; each: {id, name, slug}
            'skills' => SkillResource::collection($this->whenLoaded('skills')),

            // Req 13.4: only present when keyword was provided (relevance_score set on model)
            'relevance_score' => $this->when(
                isset($this->resource->relevance_score),
                fn () => round((float) $this->resource->relevance_score, 6)
            ),
        ];
    }

    /**
     * Cắt chuỗi tại ranh giới từ, thêm "..." nếu bị cắt.
     * Trả về null nếu đầu vào là null (Req 13.7).
     */
    private function truncatePredes(?string $text, int $maxLen): ?string
    {
        if ($text === null) {
            return null;
        }

        if (mb_strlen($text) <= $maxLen) {
            return $text;
        }

        // Cắt tại maxLen, sau đó lùi về ranh giới từ cuối cùng
        $truncated = mb_substr($text, 0, $maxLen);
        $lastSpace = mb_strrpos($truncated, ' ');

        if ($lastSpace !== false && $lastSpace > 0) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return $truncated . '...';
    }

    /**
     * Định dạng mức lương thành chuỗi hiển thị (Req 13.5).
     * salary = 0  → "Thỏa thuận"
     * salary > 0  → "15,000,000 VNĐ"
     */
    private function formatSalary(int $salary): string
    {
        if ($salary === 0) {
            return 'Thỏa thuận';
        }

        return number_format($salary) . ' VNĐ';
    }

    /**
     * Tính toán chuỗi hiển thị kinh nghiệm (Req 13.6).
     * null/null → "Không yêu cầu kinh nghiệm"
     * min only  → "Từ {min} năm"
     * max only  → "Dưới {max} năm"
     * both      → "Từ {min} đến {max} năm"
     */
    private function formatExperience(?int $min, ?int $max): string
    {
        if ($min === null && $max === null) {
            return 'Không yêu cầu kinh nghiệm';
        }

        if ($min !== null && $max === null) {
            return "Từ {$min} năm";
        }

        if ($min === null && $max !== null) {
            return "Dưới {$max} năm";
        }

        return "Từ {$min} đến {$max} năm";
    }
}
