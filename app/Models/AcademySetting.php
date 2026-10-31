<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademySetting extends Model
{
    protected $fillable = [
        'organization_name',
        'support_email',
        'support_phone',
        'updated_by',
    ];
}
