<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
  protected $model = User::class;

  protected static ?string $password;

  public function definition(): array
  {
    return [
      'user_name'      => $this->faker->name(),
      'phone_number'   => $this->faker->unique()->numerify('09#########'),
      'user_password'  => static::$password ??= Hash::make('password'),
      'user_address'   => $this->faker->address(),
      'township_id'    => 1, // adjust to valid township_id in your DB
      'current_bottles' => $this->faker->numberBetween(0, 50),
      'change_return'  => $this->faker->numberBetween(0, 20),
      'empty_collected' => $this->faker->numberBetween(0, 30),
    ];
  }
}