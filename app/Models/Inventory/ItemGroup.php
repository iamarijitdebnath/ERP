<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Models\System\Company;

class ItemGroup extends Model
{
    use HasUuids;

    protected $table = 'inventory_item_groups';

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
