<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasUuids;

    protected $table = "system_modules";

    protected $fillable = [
        'name',
        'slug',
        'sequence',
        'is_active',
    ];


    public function menuGroups():HasMany
    {
        return $this->hasMany(MenuGroup::class, 'module_id');
    }
}

