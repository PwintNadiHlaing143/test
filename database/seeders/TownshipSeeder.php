<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Township;

class TownshipSeeder extends Seeder
{
  public function run(): void
  {
    // 10 record fake data generate
    Township::factory()->count(10)->create();
  }
}
