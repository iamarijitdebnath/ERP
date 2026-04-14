<?php

namespace App\Models\HRMS;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

use App\Models\System\Company;

class Department extends Model
{
    use HasUuids;

    protected $table = 'hrms_departments';

    protected $fillable = [
        'name',
        'code',
        'company_id',
    ];
    public function company() {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function scopeForCurrentCompany($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }
}
