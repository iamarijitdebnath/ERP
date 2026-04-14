<?php

namespace App\Models\Sales;

use App\Models\System\Company;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SalesLead extends Model
{
    use HasUuids;

    protected $table = 'sales_leads';

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'customer_id',
        'company_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function inquiries()
    {
        return $this->hasMany(SalesLeadInquiry::class, 'lead_id');
    }

    public function scopeForCurrentCompany($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('mobile', 'like', "%{$term}%");
        });
    }
}
