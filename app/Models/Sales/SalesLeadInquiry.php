<?php

namespace App\Models\Sales;

use App\Models\HRMS\Employee;
use App\Models\Inventory\Item;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SalesLeadInquiry extends Model
{
    use HasUuids;

    protected $table = 'sales_leads_inquiries';

    protected $fillable = [
        'product_id',
        'employee_id',
        'lead_id',
        'source',
        'remarks',
    ];

    public function lead()
    {
        return $this->belongsTo(SalesLead::class, 'lead_id');
    }

    public function product()
    {
        return $this->belongsTo(Item::class, 'product_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function followups()
    {
        return $this->hasMany(SalesLeadFollowup::class, 'inquiry_id');
    }
}
