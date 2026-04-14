<?php

return [

    'defaults' => [
        'guard' => 'web',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'employees',
        ],
    ],

    'providers' => [
        'employees' => [
            'driver' => 'eloquent',
            'model' => App\Models\HRMS\Employee::class,
        ],
    ],

    'passwords' => [],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
