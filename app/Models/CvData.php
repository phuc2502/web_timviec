<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvData extends Model
{
    protected $table = 'cv_data';

    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'email',
        'address',
        'photo_path',
        'objective',
        'education',
        'experience',
        'projects',
        'certifications',
        'skills_text',
        'languages',
        'template',
    ];

    /**
     * JSON columns → cast thành PHP array.
     */
    protected function casts(): array
    {
        return [
            'education'      => 'array',
            'experience'     => 'array',
            'projects'       => 'array',
            'certifications' => 'array',
            'languages'      => 'array',
        ];
    }

    /**
     * CV thuộc về một User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
