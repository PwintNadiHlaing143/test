<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Township;

class TownshipSeeder extends Seeder
{
  public function run(): void
  {
    $yangonTownships = [
      'Bahan',
      'Dagon',
      'East Dagon',
      'North Dagon',
      'North Okkalapa',
      'South Dagon',
      'South Okkalapa',
      'Thingangyun',
      'Yankin',
      'Hlaing',
      'Insein',
      'Mingaladon',
      'Shwepyitha',
      'Kamayut',
      'Mayangon',
      'Pazundaung',
      'Sanchaung',
      'Tamwe',
      'Thaketa',
      'Botataung'
    ];

    foreach ($yangonTownships as $townshipName) {
      Township::factory()->create([
        'township_name' => $townshipName
      ]);
    }
  }
}