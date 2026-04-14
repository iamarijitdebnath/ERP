<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SalesLeadFollowup extends Model
{
    use HasUuids;

    protected $table = 'sales_leads_followups';

    protected $fillable = [
        'inquiry_id',
        'date',
        'follow_up_date',
        'remarks',
        'is_complete',
    ];

    protected $casts = [
        'date' => 'date',
        'follow_up_date' => 'date',
        'is_complete' => 'boolean',
    ];

    public function inquiry()
    {
        return $this->belongsTo(SalesLeadInquiry::class, 'inquiry_id');
    }
}
