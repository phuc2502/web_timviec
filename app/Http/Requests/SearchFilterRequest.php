<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates and sanitizes all 17 search/filter parameters.
 *
 * Design decision for enum-like params (job_type, work_mode, job_level,
 * company_size, sort, skill_mode): per Req 15 AC4–AC8, invalid values must
 * be silently ignored (not 422). These fields are normalized to null in
 * prepareForValidation() when the value is not in the allowed list, so the
 * validation rules only need a type check, not an 'in:' constraint.
 *
 * Only salary_min/max and exp_min/max return 422 on invalid input (Req 15 AC2–AC3).
 */
class SearchFilterRequest extends FormRequest
{
    // Allowed values for silently-ignored enum params
    private const JOB_TYPES    = ['full-time', 'part-time', 'freelance', 'internship'];
    private const WORK_MODES   = ['onsite', 'remote', 'hybrid'];
    private const JOB_LEVELS   = ['intern', 'fresher', 'junior', 'middle', 'senior', 'lead', 'manager'];
    private const COMPANY_SIZES = ['1-9', '10-49', '50-199', '200-499', '500+'];
    private const SORT_OPTIONS = ['relevance', 'newest', 'salary_desc', 'salary_asc', 'closing_soon'];

    /**
     * All users (guests and authenticated) may use the search API.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     *
     * Enum params (job_type, work_mode, etc.) are string-only here because
     * invalid values are already nulled in prepareForValidation().
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'keyword'      => ['nullable', 'string', 'max:255'],
            'job_type'     => ['nullable', 'string'],
            'work_mode'    => ['nullable', 'string'],
            'skills'       => ['nullable', 'array'],
            'skills.*'     => ['integer', 'min:1'],
            'skill_mode'   => ['nullable', 'string'],
            'address'      => ['nullable', 'string', 'max:255'],
            'city'         => ['nullable', 'string', 'max:255'],
            'salary_min'   => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'salary_max'   => ['nullable', 'integer', 'min:0', 'max:999999999', 'gte:salary_min'],
            'exp_min'      => ['nullable', 'integer', 'min:0', 'max:99'],
            'exp_max'      => ['nullable', 'integer', 'min:0', 'max:99', 'gte:exp_min'],
            'job_level'    => ['nullable', 'string'],
            'company_size' => ['nullable', 'string'],
            'sort'         => ['nullable', 'string'],
            'page'         => ['nullable', 'integer', 'min:1'],
            'per_page'     => ['nullable', 'integer'],
        ];
    }

    /**
     * Vietnamese error messages for salary and experience range validation.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // keyword
            'keyword.string'         => 'Từ khóa phải là chuỗi ký tự.',
            'keyword.max'            => 'Từ khóa không được vượt quá 255 ký tự.',

            // address
            'address.string'         => 'Địa chỉ phải là chuỗi ký tự.',
            'address.max'            => 'Địa chỉ không được vượt quá 255 ký tự.',

            // city
            'city.string'            => 'Thành phố phải là chuỗi ký tự.',
            'city.max'               => 'Thành phố không được vượt quá 255 ký tự.',

            // skills
            'skills.array'           => 'Danh sách kỹ năng phải là mảng.',
            'skills.*.integer'       => 'Mỗi kỹ năng phải là số nguyên dương.',
            'skills.*.min'           => 'ID kỹ năng phải là số nguyên dương (>= 1).',

            // salary_min — Req 15.2 (422)
            'salary_min.integer'     => 'Mức lương tối thiểu phải là số nguyên.',
            'salary_min.min'         => 'Mức lương tối thiểu phải là số không âm (>= 0).',
            'salary_min.max'         => 'Mức lương tối thiểu không được vượt quá 999.999.999.',

            // salary_max — Req 15.2 (422); gte message per Req 6.4
            'salary_max.integer'     => 'Mức lương tối đa phải là số nguyên.',
            'salary_max.min'         => 'Mức lương tối đa phải là số không âm (>= 0).',
            'salary_max.max'         => 'Mức lương tối đa không được vượt quá 999.999.999.',
            'salary_max.gte'         => 'Mức lương tối thiểu không được lớn hơn mức lương tối đa.',

            // exp_min — Req 15.3 (422)
            'exp_min.integer'        => 'Số năm kinh nghiệm tối thiểu phải là số nguyên.',
            'exp_min.min'            => 'Số năm kinh nghiệm tối thiểu phải là số không âm (>= 0).',
            'exp_min.max'            => 'Số năm kinh nghiệm tối thiểu không được vượt quá 99.',

            // exp_max — Req 15.3 (422); gte message per Req 7.4
            'exp_max.integer'        => 'Số năm kinh nghiệm tối đa phải là số nguyên.',
            'exp_max.min'            => 'Số năm kinh nghiệm tối đa phải là số không âm (>= 0).',
            'exp_max.max'            => 'Số năm kinh nghiệm tối đa không được vượt quá 99.',
            'exp_max.gte'            => 'Kinh nghiệm tối thiểu không được lớn hơn kinh nghiệm tối đa.',

            // page / per_page
            'page.integer'           => 'Số trang phải là số nguyên.',
            'page.min'               => 'Số trang phải là số nguyên dương (>= 1).',
            'per_page.integer'       => 'Số kết quả mỗi trang phải là số nguyên.',
            'per_page.min'           => 'Số kết quả mỗi trang phải từ 5 đến 50.',
            'per_page.max'           => 'Số kết quả mỗi trang phải từ 5 đến 50.',
        ];
    }

    /**
     * Sanitize and normalize input before validation runs.
     *
     * Actions performed:
     * 1. Trim + truncate keyword / address / city to 255 chars (Req 15.12, 5.5)
     * 2. Null out enum params with invalid values — silently ignore per Req 15 AC4–8
     * 3. Normalize skill_mode: fallback to 'and' (Req 15.8)
     * 4. Truncate skills array to first 15 elements (Req 4.5, 15.10)
     * 5. Fallback page to 1 when not a positive integer (Req 15.11)
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        // 1. Trim + truncate keyword
        if ($this->has('keyword')) {
            $keyword = $this->input('keyword');
            if (is_string($keyword)) {
                $merge['keyword'] = mb_substr(trim($keyword), 0, 255);
            }
        }

        // 1. Trim + truncate address
        if ($this->has('address')) {
            $address = $this->input('address');
            if (is_string($address)) {
                $merge['address'] = mb_substr(trim($address), 0, 255);
            }
        }

        // 1. Trim + truncate city
        if ($this->has('city')) {
            $city = $this->input('city');
            if (is_string($city)) {
                $merge['city'] = mb_substr(trim($city), 0, 255);
            }
        }

        // 2. Null out invalid enum values (silently ignore per Req 15 AC4–7)
        $jobType = $this->input('job_type');
        if ($jobType !== null && !in_array(strtolower((string) $jobType), self::JOB_TYPES, true)) {
            $merge['job_type'] = null;
        } elseif ($jobType !== null) {
            $merge['job_type'] = strtolower((string) $jobType);
        }

        $workMode = $this->input('work_mode');
        if ($workMode !== null && !in_array(strtolower((string) $workMode), self::WORK_MODES, true)) {
            $merge['work_mode'] = null;
        } elseif ($workMode !== null) {
            $merge['work_mode'] = strtolower((string) $workMode);
        }

        $jobLevel = $this->input('job_level');
        if ($jobLevel !== null && !in_array(strtolower((string) $jobLevel), self::JOB_LEVELS, true)) {
            $merge['job_level'] = null;
        } elseif ($jobLevel !== null) {
            $merge['job_level'] = strtolower((string) $jobLevel);
        }

        $companySize = $this->input('company_size');
        if ($companySize !== null && !in_array((string) $companySize, self::COMPANY_SIZES, true)) {
            $merge['company_size'] = null;
        }

        $sort = $this->input('sort');
        if ($sort !== null && !in_array(strtolower((string) $sort), self::SORT_OPTIONS, true)) {
            $merge['sort'] = null;
        } elseif ($sort !== null) {
            $merge['sort'] = strtolower((string) $sort);
        }

        // 3. Normalize skill_mode: fallback to 'and' (Req 15.8)
        $skillMode = $this->input('skill_mode');
        if (!in_array($skillMode, ['and', 'or'], true)) {
            $merge['skill_mode'] = 'and';
        }

        // 4. Sanitize skills: only keep positive integers and truncate to max 15 elements (Req 4.5, 15.9, 15.10)
        $skills = $this->input('skills');
        if (is_array($skills)) {
            $filteredSkills = array_values(array_filter($skills, function ($value) {
                return is_numeric($value) && (int) $value > 0 && (float) $value == (int) $value;
            }));
            $filteredSkills = array_map('intval', $filteredSkills);
            if (count($filteredSkills) > 15) {
                $filteredSkills = array_slice($filteredSkills, 0, 15);
            }
            $merge['skills'] = $filteredSkills;
        }

        // 5. Fallback page to 1 when not a valid positive integer (Req 15.11)
        $page = $this->input('page');
        if (!is_numeric($page) || (int) $page < 1) {
            $merge['page'] = 1;
        }

        // 6. Normalize per_page: if outside [5, 50], null it so Service defaults to 15 (Req 10.3)
        $perPage = $this->input('per_page');
        if ($perPage !== null && is_numeric($perPage)) {
            $perPageInt = (int) $perPage;
            if ($perPageInt < 5 || $perPageInt > 50) {
                $merge['per_page'] = null;
            }
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }
}
