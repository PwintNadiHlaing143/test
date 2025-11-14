<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use Illuminate\Support\Facades\Hash;
use App\Models\DeliveryStaff;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryStaff>
 */
class DeliveryStaffFactory extends Factory
{
  protected $model = DeliveryStaff::class;

  protected static ?string $password;

  public function definition(): array
  {
    return [
      'staff_name'      => $this->faker->name(),
      'staff_phone'     => $this->faker->unique()->numerify('09#########'),
      'staff_password'  =>  static::$password ??= Hash::make('password'),
      'staff_address'   => $this->faker->address(),
      'group_id'        => $this->faker->numberBetween(1, 10), // Random group_id from 1 to 10
      'staff_status'    => $this->faker->boolean(90) // 90% chance of true
    ];
  }
}