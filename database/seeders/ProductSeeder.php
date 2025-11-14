<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
  public function run(): void
  {
    DB::table('products')->insert([
      [
        'owner_id' => 1,
        'product_name' => '1Liter',
        'unit_price' => 700,
        'current_stock' => 100,
        'description' => '1Liter water with pH 8.5 for better hydration',
        'product_image' => '1Liter_remove.png',
        'product_status' => true,
        'created_at' => now(),
        'updated_at' => now(),
      ],

      [
        'owner_id' => 1,
        'product_name' => '5Liter',
        'unit_price' => 1800,
        'current_stock' => 75,
        'description' => '5Liter water with pH 8.5 for better hydration',
        'product_image' => '5Liter_remove.png',
        'product_status' => true,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'owner_id' => 1,
        'product_name' => '20Liter',
        'unit_price' => 2000,
        'current_stock' => 50,
        'description' => '20Liter water with pH 8.5 for better hydration',
        'product_image' => '20Liter_remove.png',
        'product_status' => true,
        'created_at' => now(),
        'updated_at' => now(),
      ],


    ]);
  }
}