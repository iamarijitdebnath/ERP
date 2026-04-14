<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasUuids;

    protected $table = 'inventory_transaction';

    protected $fillable = [
        'type',
        'type_id', 
        'from_warehouse_id',
        'to_warehouse_id',
        'transaction_date',
        'company_id',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'inventory_transaction_id');
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\System\Company::class, 'company_id');
    }

    public function scopeForCurrentCompany($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }
}
