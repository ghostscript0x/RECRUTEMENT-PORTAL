<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['action', 'subject_type', 'subject_id', 'user_id', 'details', 'ip_address', 'created_at'];

    protected function casts(): array
    {
        return ['details' => 'array', 'created_at' => 'datetime'];
    }

    /** Single call used across the whole application for audit trails. */
    public static function record(string $action, ?Model $subject = null, array $details = []): self
    {
        return static::create([
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'user_id' => Auth::id(),
            'details' => $details ?: null,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
