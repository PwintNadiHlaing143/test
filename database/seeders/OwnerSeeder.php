<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Insert default admin 
    DB::table('owner')->insert([
      'owner_name'     => 'Pwint',
      'owner_phone'    => '09669211320',
      'owner_password' => Hash::make('password123'),
    ]);
  }
}