<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SupervisorSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */ public function run(): void
  {
    DB::table('supervisors')->insert([
      [
        'supervisor_name'     => 'Wathon',
        'supervisor_phone'    => '09666248377',
        'supervisor_password' => Hash::make('wathon123'),
        'supervisor_address' => 'Belin',
        'owner_id' => 1, // Add owner_id if needed
        'supervisor_status' => true,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'supervisor_name'     => 'Phoo',
        'supervisor_phone'    => '09672122015',
        'supervisor_password' => Hash::make('phoo123'),
        'supervisor_address' => 'Yangon',
        'owner_id' => 1, // Add owner_id if needed
        'supervisor_status' => true,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'supervisor_name'     => 'Chan',
        'supervisor_phone'    => '09674762758',
        'supervisor_password' => Hash::make('chan123'),
        'supervisor_address' => 'Yangon',
        'owner_id' => 1, // Add owner_id if needed
        'supervisor_status' => true,
        'created_at' => now(),
        'updated_at' => now(),
      ]
    ]);
  }
}