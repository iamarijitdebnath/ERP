<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ItemQuantity extends Model
{
    use HasFactory;

    protected $table = 'inventory_item_quantities';

    protected $fillable = [
        'id',
        'item_id',
        'uom_id',
        'warehouse_id',
        'batch_no',
        'lot_no',
        'serial_no',
        'quantity',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function uom()
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
