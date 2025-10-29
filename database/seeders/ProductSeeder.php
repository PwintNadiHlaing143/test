<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProductSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    DB::table('products')->insert([
      [
        'owner_id' => 1,
        'product_name' => 'Premium Drinking Water',
        'unit_price' => 1500.00,
        'current_stock' => 100,
        'description' => '1L Premium quality purified drinking water',
        'product_image' => 'premium_water.jpg',
        'product_status' => true,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'owner_id' => 1,
        'product_name' => 'Mineral Water',
        'unit_price' => 1200.00,
        'current_stock' => 150,
        'description' => '500ml Natural mineral water with essential minerals',
        'product_image' => 'mineral_water.jpg',
        'product_status' => true,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'owner_id' => 1,
        'product_name' => 'Alkaline Water',
        'unit_price' => 2000.00,
        'current_stock' => 75,
        'description' => '1L Alkaline water with pH 8.5 for better hydration',
        'product_image' => 'alkaline_water.jpg',
        'product_status' => true,
        'created_at' => now(),
        'updated_at' => now(),
      ]
    ]);
  }
}