<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuGroup extends Model
{
    use HasUuids;
    protected $table = "system_menu_groups";

     protected $fillable = [
        'id',
        'name',
        'color',
        'sequence',
        'is_active',
        'module_id',
    ];
    public function module():BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function menus():HasMany
    {
        return $this->hasMany(Menu::class, 'group_id');
    }
}
