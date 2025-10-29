<?php

return [
  'defaults' => [
    'guard' => 'api', // ✅ Change from 'web' to 'api'
    'passwords' => 'owners', // ✅ Change to 'owners' (or keep 'users' if you prefer)
  ],

  'guards' => [
    'web' => [
      'driver' => 'session',
      'provider' => 'users',
    ],

    'api' => [
      'driver' => 'passport',
      'provider' => 'owners', // ✅ Change from 'users' to 'owners'
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

    // ✅ ADD these password reset configs
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