<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use Illuminate\Support\Facades\DB;


class OrderSeeder extends Seeder
{
  public function run(): void
  {
    $orders = [
      [
        'owner_id' => 1,
        'product_id' => 1,
        'user_id' => 11,
        'order_date' => '2024-01-01 08:30:00', // Old date
        'order_quantity' => 5,
        'total_amount' => 5000,
        'order_status' => 'completed',
        'sold_price' => 1000,
        'cash_collected' => 6000,
        'change_returned' => 1000,
        'empty_collected' => 3,
        'notes' => 'Please deliver in the morning'
      ],
      [
        'owner_id' => 1,
        'product_id' => 2,
        'user_id' => 11,
        'order_date' => '2024-01-02 14:15:00', // Old date
        'order_quantity' => 10,
        'total_amount' => 15000,
        'order_status' => 'completed',
        'sold_price' => 1500,
        'cash_collected' => 15000,
        'change_returned' => 0,
        'empty_collected' => 8,
        'notes' => 'Customer requested evening delivery'
      ],
      [
        'owner_id' => 1,
        'product_id' => 1,
        'user_id' => 11,
        'order_date' => '2024-01-03 10:45:00', // Old date
        'order_quantity' => 3,
        'total_amount' => 3000,
        'order_status' => 'completed',
        'sold_price' => 1000,
        'cash_collected' => 0,
        'change_returned' => 0,
        'empty_collected' => 0,
        'notes' => 'New customer'
      ],
      [
        'owner_id' => 1,
        'product_id' => 3,
        'user_id' => 11,
        'order_date' => '2024-01-04 16:20:00', // Old date
        'order_quantity' => 7,
        'total_amount' => 10500,
        'order_status' => 'completed',
        'sold_price' => 1500,
        'cash_collected' => 0,
        'change_returned' => 0,
        'empty_collected' => 0,
        'notes' => 'Regular customer - priority delivery'
      ],

      [
        'owner_id' => 1,
        'product_id' => 2,
        'user_id' => 11,
        'order_quantity' => 2,
        'total_amount' => 3000,
        'order_status' => 'completed',
        'sold_price' => 1500,
        'cash_collected' => 0,
        'change_returned' => 0,
        'empty_collected' => 0,
        'notes' => 'Customer cancelled order'
      ]
    ];

    foreach ($orders as $order) {
      DB::table('orders')->insert($order);
    }
  }
}