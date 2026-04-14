<?php

namespace App\Models\Inventory;

use App\Models\HRMS\Employee;
use App\Models\System\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryGoodsReceiptNote extends Model
{
    use HasFactory;

    protected $table = 'inventory_goods_receipt_note';
    
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = Str::uuid();
            
            if (empty($model->doc_no)) {
                $model->doc_no = static::generateDocNo();
            }
        });
    }

    public static function generateDocNo()
    {
        $year = now()->format('y');
        $prefix = "GRN/{$year}/";
        
        $lastRecord = self::where('doc_no', 'like', "{$prefix}%")
            ->orderBy('doc_no', 'desc')
            ->first();

        if ($lastRecord) {
            $lastSequence = (int) substr($lastRecord->doc_no, strrpos($lastRecord->doc_no, '/') + 1);
            $newSequence = str_pad($lastSequence + 1, 7, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '0000001';
        }

        return $prefix . $newSequence;
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(Employee::class, 'received_by');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function scopeForCurrentCompany($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }
}
