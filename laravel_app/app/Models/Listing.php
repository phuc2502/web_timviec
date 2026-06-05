<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'address',
        'job_type',
        'level',
        'salary_min',
        'salary_max',
        'is_negotiable',
        'hide_salary',
        'application_close_date',
        'start_date',
        'vacancy_count',
        'contact_email',
        'contact_phone',
        'jd_file_path',
        'publish_mode',
        'scheduled_at',
        'status',
        'rejection_reason',
        'rejected_at',
        'archived_reason',
    ];

    protected $casts = [
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'is_negotiable' => 'boolean',
        'hide_salary' => 'boolean',
        'application_close_date' => 'date',
        'start_date' => 'date',
        'scheduled_at' => 'datetime',
        'rejected_at' => 'datetime',
        'vacancy_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'listing_skill');
    }

    public function views(): HasMany
    {
        return $this->hasMany(ListingView::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ListingReport::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ListingAuditLog::class);
    }
}
