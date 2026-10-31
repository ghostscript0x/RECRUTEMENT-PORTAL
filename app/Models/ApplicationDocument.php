<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDocument extends Model
{
    protected $fillable = ['application_id', 'type', 'path', 'original_name', 'mime_type', 'size'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function humanSize(): string
    {
        $bytes = $this->size;

        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, 1).' '.$unit;
            }
            $bytes /= 1024;
        }

        return round($bytes, 1).' TB';
    }
}
