<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Str;

class LoginLog extends Model
{
    use HasUuids;
    protected $table = 'system_login_logs';

    protected $fillable = [
        'email', 'status', 'ip_address', 'employee_id'
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();   // <-- REQUIRED
            }
        });
    }
}
