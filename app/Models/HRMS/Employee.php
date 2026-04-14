<?php

namespace App\Models\HRMS;

// use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Models\System\Company;
use App\Models\HRMS\Department;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Authenticatable {
    // use HasUuids;
    
    protected $table = "hrms_employees";
    
    protected $fillable = [
        'salutation', 
        'first_name',
        'last_name',
        'gender',
        'code',
        'employment_type',
        'payment_type',
        'email',
        'password',
        'date_of_birth',
        'date_of_joining',
        'notice_period',
        'date_of_resignation',
        'date_of_release',
        'department_id',
        'under_id',  
        'company_id',
        'is_active',
        'deleted_at',
    ];

    protected $hidden = [
        'password'
    ];
    public function permissions()
    {
        return $this->hasMany(EmployeePermission::class, 'employee_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function reportingTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'under_id');
    }

    public function scopeForCurrentCompany($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }
}
