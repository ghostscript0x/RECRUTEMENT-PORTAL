<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['application_id', 'template_key', 'to_email', 'subject', 'status', 'error', 'context', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'context' => 'array'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
