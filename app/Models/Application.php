<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\Role as RoleEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class Application extends Model
{
    protected $fillable = [
        'reference', 'position_id', 'assigned_reviewer_id',
        'first_name', 'middle_name', 'last_name', 'email', 'phone', 'date_of_birth', 'gender',
        'state_of_origin_id', 'origin_lga_id', 'residence_state_id', 'residence_lga_id', 'residential_address',
        'highest_qualification', 'institution', 'course_of_study', 'graduation_year', 'additional_qualifications',
        'has_leadership', 'leadership_organization', 'leadership_position', 'leadership_duration', 'leadership_description',
        'about', 'status', 'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date:Y-m-d',
            'graduation_year' => 'integer',
            'has_leadership' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    /* ----------------------------------------------------------- Relations */

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function assignedReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_reviewer_id');
    }

    public function stateOfOrigin(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_of_origin_id');
    }

    public function originLga(): BelongsTo
    {
        return $this->belongsTo(LocalGovernment::class, 'origin_lga_id');
    }

    public function residenceState(): BelongsTo
    {
        return $this->belongsTo(State::class, 'residence_state_id');
    }

    public function residenceLga(): BelongsTo
    {
        return $this->belongsTo(LocalGovernment::class, 'residence_lga_id');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(ApplicationSkill::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class)->latest();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ApplicationNote::class)->latest();
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    /* ------------------------------------------------------------ Helpers */

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function statusEnum(): ApplicationStatus
    {
        return ApplicationStatus::from($this->status);
    }

    /* ------------------------------------------------------------- Scopes */

    /**
     * Admin search + filters. Used by the applications table AND the export,
     * so exports always respect whatever filters are active.
     */
    public function scopeFiltered(Builder $query, Request $request): Builder
    {
        $term = trim((string) $request->input('q'));

        return $query
            ->with(['position:id,title', 'residenceState:id,name'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('position_id'), fn ($q) => $q->where('position_id', $request->input('position_id')))
            ->when($request->filled('state_id'), fn ($q) => $q->where('residence_state_id', $request->input('state_id')))
            ->when($request->filled('lga_id'), fn ($q) => $q->where('residence_lga_id', $request->input('lga_id')))
            ->when($request->filled('qualification'), fn ($q) => $q->where('highest_qualification', $request->input('qualification')))
            ->when($request->filled('gender'), fn ($q) => $q->where('gender', $request->input('gender')))
            ->when($request->filled('leadership'), fn ($q) => $q->where('has_leadership', $request->input('leadership') === 'yes'))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('submitted_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('submitted_at', '<=', $request->input('to')))
            ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('reference', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%"))
            );
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->role?->name === RoleEnum::Reviewer->value) {
            $query->where('assigned_reviewer_id', $user->id);
        }

        return $query;
    }
}
