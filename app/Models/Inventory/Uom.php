<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Models\System\Company;

class Uom extends Model
{
    use HasUuids;

    protected $table = 'inventory_uoms';

    protected $fillable = [
        'name',
        'category',
        'si_unit',
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
