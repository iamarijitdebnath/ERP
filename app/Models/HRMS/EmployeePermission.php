<?php

namespace App\Models\HRMS;

use App\Models\System\Menu;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePermission extends Model
{
    use HasUuids;

    protected $table = 'hrms_employee_permissions';

    protected $fillable = [
        'employee_id',
        'menu_id',
        'can_create',
        'can_read',
        'can_update',
        'can_delete',
    ];

    protected $casts = [
        'can_create' => 'boolean',
        'can_read' => 'boolean',
        'can_update' => 'boolean',
        'can_delete' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
