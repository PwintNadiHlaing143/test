<?php

return [



  'defaults' => [
    'guard' => 'web',
    'passwords' => 'users',
  ],



  'guards' => [
    'web' => [
      'driver' => 'session',
      'provider' => 'users',
    ],
    'api' => [
      'driver' => 'passport',
      'provider' => 'users',
    ],

    //for supervisor
    'supervisor-api' => [
      'driver' => 'passport',
      'provider' => 'supervisors',
    ],
    //for supervisor
    'owner-api' => [
      'driver' => 'passport',
      'provider' => 'owners',
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

  ],



  'passwords' => [
    'users' => [
      'provider' => 'users',
      'table' => 'password_reset_tokens',
      'expire' => 60,
      'throttle' => 60,
    ],
  ],



  'password_timeout' => 10800,

];
