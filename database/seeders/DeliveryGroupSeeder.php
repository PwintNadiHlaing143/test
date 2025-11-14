<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DeliveryGroupSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    DB::table('delivery_group')->insert([
      [
        'group_name' => 'North Team',
        'supervisor_id' => 3,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'group_name' => 'South Team',
        'supervisor_id' => 3,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'group_name' => 'East Team',
        'supervisor_id' => 3,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'group_name' => 'West Team',
        'supervisor_id' => 3,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'group_name' => 'Central Team',
        'supervisor_id' => 3,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'group_name' => 'Downtown Team',
        'supervisor_id' => 3,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'group_name' => 'Uptown Team',
        'supervisor_id' => 3,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'group_name' => 'Riverside Team',
        'supervisor_id' => 3,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'group_name' => 'Hillside Team',
        'supervisor_id' => 3,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'group_name' => 'Lakeside Team',
        'supervisor_id' => 3,
        'created_at' => now(),
        'updated_at' => now(),
      ]
    ]);
  }
}