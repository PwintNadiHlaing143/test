<?php

return [
  'defaults' => [
    'guard' => 'owner-api',
    'passwords' => 'owners',
  ],

  'guards' => [
    'web' => [
      'driver' => 'session',
      'provider' => 'users',
    ],

    'user-api' => [
      'driver' => 'passport',
      'provider' => 'users',
    ],

    //for supervisor
    'supervisor-api' => [
      'driver' => 'passport',
      'provider' => 'supervisors',
    ],

    //for owner
    'owner-api' => [
      'driver' => 'passport',
      'provider' => 'owners',
    ],


    'deliveryStaff-api' => [
      'driver' => 'passport',
      'provider' => 'deliveryStaffs',
    ],
  ],

  'providers' => [
    'users' => [
      'driver' => 'eloquent',
      'model' => App\Models\User::class,
    ],

    'owners' => [
      'driver' => 'eloquent',
      'model' => App\Models\Owner::class,
    ],

    'supervisors' => [
      'driver' => 'eloquent',
      'model' => App\Models\Supervisor::class,
    ],
    'deliveryStaffs' => [
      'driver' => 'eloquent',
      'model' => App\Models\DeliveryStaff::class,
    ],
  ],

  'passwords' => [
    'users' => [
      'provider' => 'users',
      'table' => 'password_reset_tokens',
      'expire' => 60,
      'throttle' => 60,
    ],


    'owners' => [
      'provider' => 'owners',
      'table' => 'password_reset_tokens',
      'expire' => 60,
      'throttle' => 60,
    ],

    'supervisors' => [
      'provider' => 'supervisors',
      'table' => 'password_reset_tokens',
      'expire' => 60,
      'throttle' => 60,
    ],
  ],

  'password_timeout' => 10800,
];