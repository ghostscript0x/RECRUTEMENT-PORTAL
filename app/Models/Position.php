<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    public const STATUSES = ['draft' => 'Draft', 'published' => 'Published', 'closed' => 'Closed', 'archived' => 'Archived'];

    protected $fillable = [
        'title', 'department', 'description', 'requirements', 'opens_at', 'closes_at',
        'status', 'required_documents', 'optional_documents',
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'date:Y-m-d',
            'closes_at' => 'date:Y-m-d',
            'required_documents' => 'array',
            'optional_documents' => 'array',
        ];
    }

    /** Positions that are live and accepting new applications right now. */
    public function scopeAccepting(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('opens_at')->orWhere('opens_at', '<=', now()->toDateString()))
            ->where(fn ($q) => $q->whereNull('closes_at')->orWhere('closes_at', '>=', now()->toDateString()));
    }

    public function isAccepting(): bool
    {
        return $this->status === 'published'
            && (! $this->opens_at || $this->opens_at->lte(now()))
            && (! $this->closes_at || $this->closes_at->gte(now()));
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /** Combined document requirement configuration for the upload step. */
    public function documentRequirements(): array
    {
        return array_merge(
            array_map(fn ($d) => $d + ['required' => true], $this->required_documents ?? []),
            array_map(fn ($d) => $d + ['required' => false], $this->optional_documents ?? []),
        );
    }
}
