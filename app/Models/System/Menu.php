<?php

namespace App\Models\System;

use App\Models\HRMS\EmployeePermission;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Menu extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'system_menus';

    protected $fillable = [
        'name',
        'route',
        'sequence',
        'is_active',
        'group_id',
    ];

    public function group():BelongsTo
    {
        return $this->belongsTo(MenuGroup::class, 'group_id');
    }
    public function permissions()
    {
        return $this->hasMany(EmployeePermission::class, 'menu_id', 'id');
    }


}
