<?php

namespace Database\Factories;

use App\Models\Township;
use Illuminate\Database\Eloquent\Factories\Factory;

class TownshipFactory extends Factory
{
  protected $model = Township::class;

  public function definition(): array
  {
    return [
      'township_name' => $this->faker->city(),
    ];
  }
}
