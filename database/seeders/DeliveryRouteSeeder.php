<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DeliveryRoute;
use Carbon\Carbon;

class DeliveryRouteSeeder extends Seeder
{

  public function run(): void
  {
    $deliveryRoutes = [
      [
        'group_id' => 1,
        'order_id' => 1,
        'supervisor_id' => 3,
        'township_id' => 1,
        'delivery_status' => 'pending',
        'delivery_date' => Carbon::today()->addDays(1),

      ],
      [
        'group_id' => 1,
        'order_id' => 2,
        'supervisor_id' => 3,
        'township_id' => 2,
        'delivery_status' => 'pending',
        'delivery_date' => Carbon::today()->addDays(2),
      ],
      [
        'group_id' => 2,
        'order_id' => 3,
        'supervisor_id' => 3,
        'township_id' => 3,
        'delivery_status' => 'completed',
        'delivery_date' => Carbon::today(),

      ],
      [
        'group_id' => 2,
        'order_id' => 4,
        'supervisor_id' => 3,
        'township_id' => 4,
        'delivery_status' => 'completed',
        'delivery_date' => Carbon::yesterday(),
      ],
      [
        'group_id' => 1,
        'order_id' => 5,
        'supervisor_id' => 3,
        'township_id' => 5,
        'delivery_status' => 'pending',
        'delivery_date' => Carbon::today()->addDays(3),

      ]
    ];

    foreach ($deliveryRoutes as $route) {
      DeliveryRoute::create($route);
    }
  }
}
