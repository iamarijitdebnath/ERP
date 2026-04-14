<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Company extends Model {
    
    use HasUuids;

    protected $table = "system_companies";

    protected $fillable = [
        'name',
        'is_active',
    ];
}