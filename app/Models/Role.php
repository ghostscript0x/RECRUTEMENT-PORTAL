<?php

namespace App\Models;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name', 'label'];

    public static function for(RoleEnum $role): self
    {
        return static::firstWhere('name', $role->value) ?? static::create([
            'name' => $role->value,
            'label' => $role->label(),
        ]);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
