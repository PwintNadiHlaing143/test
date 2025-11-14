<?php

namespace Database\Seeders;

use App\Models\DeliveryStaff;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DeliveryStaffSeeder extends Seeder
{

  public function run(): void
  {
    DeliveryStaff::factory()->count(10)->create();
  }
}