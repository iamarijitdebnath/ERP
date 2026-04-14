<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasUuids;

    protected $table = 'inventory_transaction_item';

    protected $fillable = [
        'inventory_transaction_id',
        'item_id',
        'uom_id',
        'quantity',
        'price',
        'cgst',
        'sgst',
        'igst',
        'cess',
        'batch_no',
        'lot_no',
        'serial_no',
        'exp_date',
    ];

    protected $casts = [
        'exp_date' => 'date',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'inventory_transaction_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function uom()
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }
}
