<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $this->call([
      OwnerSeeder::class,
      TownshipSeeder::class,
      UserSeeder::class,
      SupervisorSeeder::class,
      DeliveryGroupSeeder::class,
      DeliveryStaffSeeder::class,
      ProductSeeder::class,
      OrderSeeder::class,
      DeliveryRouteSeeder::class,

    ]);
  }
}