<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Models\System\Company;

class Item extends Model
{
    use HasUuids;

    protected $table = 'inventory_items';

    protected $fillable = [
        'code',
        'sku',
        'name',
        'group_id',
        'description',
        'attributes',
        'uom_id',
        'acquire',
        'barcode',
        'has_expiry',
        'tracking',
        'is_active',
        'master_id',
        'company_id',
    ];

    protected $casts = [
        'attributes' => 'array',
        'has_expiry' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function group()
    {
        return $this->belongsTo(ItemGroup::class, 'group_id');
    }

    public function uom()
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }
    public function master()
    {
        return $this->belongsTo(Item::class, 'master_id');
    }

    public function variants()
    {
        return $this->hasMany(Item::class, 'master_id');
    }

    public function scopeForCurrentCompany($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->whereLike('name', "%{$term}%")
              ->orWhereLike('code', "%{$term}%")
              ->orWhereLike('sku', "%{$term}%");
        });
    }
}
