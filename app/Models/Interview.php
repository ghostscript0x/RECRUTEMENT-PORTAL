<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    public const CHANNELS = [
        'google_meet' => 'Google Meet',
        'zoom' => 'Zoom',
        'whatsapp' => 'WhatsApp',
        'phone' => 'Phone call',
        'in_person' => 'In person',
        'other' => 'Other',
    ];

    protected $fillable = [
        'application_id',
        'created_by',
        'scheduled_at',
        'duration_minutes',
        'mode',
        'location',
        'instructions',
        'email_subject',
        'email_message',
        'status',
        'reschedule_token_hash',
        'reschedule_token',
        'reschedule_reason',
        'reschedule_requested_at',
        'invitation_sent_at',
    ];

    protected $hidden = ['reschedule_token', 'reschedule_token_hash'];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'immutable_datetime',
            'reschedule_requested_at' => 'immutable_datetime',
            'invitation_sent_at' => 'immutable_datetime',
            'reschedule_token' => 'encrypted',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function channelLabel(): string
    {
        return self::CHANNELS[$this->mode] ?? 'Other';
    }

    public function isReadyForInvitation(): bool
    {
        return $this->scheduled_at?->isFuture() === true
            && isset(self::CHANNELS[$this->mode])
            && filled($this->location)
            && filled($this->email_subject)
            && filled($this->email_message)
            && filled($this->reschedule_token);
    }
}
