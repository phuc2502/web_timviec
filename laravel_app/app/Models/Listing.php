<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Listing extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
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
        'address',
        'salary',
        'feature_image',
        'application_close_date',
        'status',
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
    ];

    /**
     * Get the employer (user) that owns this listing.
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the skills associated with this listing.
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'listing_skill');
    }

    /**
     * Scope a query to only include active listings.
     * Active = status is 'open' AND application_close_date >= today.
     *
     * Uses CURDATE() on MySQL and date('now') on SQLite for test compatibility.
     */
    public function scopeActive(Builder $query): Builder
    {
        if (config('database.default') === 'sqlite') {
            return $query->where('status', 'open')
                         ->whereDate('application_close_date', '>=', now()->toDateString());
        }

        return $query->where('status', 'open')
                     ->whereDate('application_close_date', '>=', DB::raw('CURDATE()'));
    }
}
