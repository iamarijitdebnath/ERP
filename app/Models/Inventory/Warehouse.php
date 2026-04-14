<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Models\System\Company;

class Warehouse extends Model
{
    use HasUuids;

    protected $table = 'inventory_warehouses';

    protected $fillable = [
        'name',
        'code',
        'address1',
        'address2',
        'city',
        'state',
        'contact_person',
        'mobile_no',
        'email',
        'company_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function scopeForCurrentCompany($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }
}
