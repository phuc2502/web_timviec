<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Listing extends Model
{
    use SoftDeletes, HasFactory;

    protected static function booted()
    {
        static::creating(function ($listing) {
            if (empty($listing->slug)) {
                $listing->slug = \Illuminate\Support\Str::slug($listing->title) . '-' . \Illuminate\Support\Str::random(6);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'predes',
        'description',
        'requirements',
        'benefits',
        'job_type',
        'work_mode',
        'experience_years_min',
        'experience_years_max',
        'job_level',
        'level',
        'address',
        'salary',
        'salary_min',
        'salary_max',
        'is_negotiable',
        'hide_salary',
        'feature_image',
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requirements'           => 'array',
        'benefits'               => 'array',
        'application_close_date' => 'date',
        'salary'                 => 'integer',
        'experience_years_min'   => 'integer',
        'experience_years_max'   => 'integer',
        'salary_min'             => 'decimal:2',
        'salary_max'             => 'decimal:2',
        'is_negotiable'          => 'boolean',
        'hide_salary'            => 'boolean',
        'start_date'             => 'date',
        'scheduled_at'           => 'datetime',
        'rejected_at'            => 'datetime',
        'vacancy_count'          => 'integer',
    ];

    /**
     * Get the employer (user) that owns this listing (local).
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user that owns this listing (remote).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that owns this listing.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the skills associated with this listing.
     */
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

    /**
     * Scope a query to only include active listings.
     * Supports both:
     * - Local: status = 'open' AND application_close_date >= today
     * - Remote: status = 'active'
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where(function ($q1) {
                if (config('database.default') === 'sqlite') {
                    $q1->where('status', 'open')
                       ->whereDate('application_close_date', '>=', now()->toDateString());
                } else {
                    $q1->where('status', 'open')
                       ->whereDate('application_close_date', '>=', DB::raw('CURDATE()'));
                }
            })->orWhere('status', 'active');
        });
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    public function scopeByEmployer($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeSearch($query, string $keyword)
    {
        return $query->whereFullText(['title', 'description'], $keyword);
    }

    // Accessors
    public function getFormattedSalaryAttribute(): string
    {
        if ($this->is_negotiable) {
            return 'Thỏa thuận';
        }

        if ($this->hide_salary) {
            return 'Liên hệ';
        }

        if ($this->salary_min && $this->salary_max) {
            return number_format($this->salary_min) . ' - ' . number_format($this->salary_max) . ' VNĐ';
        }

        return 'Chưa xác định';
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        return now()->diffInDays($this->application_close_date, false);
    }
}
