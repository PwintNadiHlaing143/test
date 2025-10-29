<?php

namespace Database\Seeders;

use App\Models\DeliveryStaff;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DeliveryStaffSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    DeliveryStaff::factory()->count(10)->create();
  }
}