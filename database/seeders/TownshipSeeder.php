<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Township;

class TownshipSeeder extends Seeder
{
  public function run(): void
  {
    // Generate 10 fake township
    Township::factory()->count(10)->create();
  }
}