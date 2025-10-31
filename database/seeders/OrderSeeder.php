<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Orders;
use Illuminate\Support\Facades\DB;


class OrderSeeder extends Seeder
{
  public function run(): void
  {
    $orders = [
      [
        'owner_id' => 1,
        'product_id' => 1,
        'user_id' => 1,
        'order_quantity' => 5,
        'total_amount' => 5000.00,
        'order_status' => 'pending',
        'sold_price' => 1000.00,
        'cash_collected' => 6000.00,
        'change_returned' => 1000.00,
        'empty_collected' => 3,
        'notes' => 'Please deliver in the morning'
      ],
      [
        'owner_id' => 1,
        'product_id' => 2,
        'user_id' => 2,
        'order_quantity' => 10,
        'total_amount' => 15000.00,
        'order_status' => 'pending',
        'sold_price' => 1500.00,
        'cash_collected' => 15000.00,
        'change_returned' => 0.00,
        'empty_collected' => 8,
        'notes' => 'Customer requested evening delivery'
      ],
      [
        'owner_id' => 1,
        'product_id' => 1,
        'user_id' => 3,
        'order_quantity' => 3,
        'total_amount' => 3000.00,
        'order_status' => 'processing',
        'sold_price' => 1000.00,
        'cash_collected' => 0.00,
        'change_returned' => 0.00,
        'empty_collected' => 0,
        'notes' => 'New customer'
      ],
      [
        'owner_id' => 1,
        'product_id' => 3,
        'user_id' => 4,
        'order_quantity' => 7,
        'total_amount' => 10500.00,
        'order_status' => 'pending',
        'sold_price' => 1500.00,
        'cash_collected' => 0.00,
        'change_returned' => 0.00,
        'empty_collected' => 0,
        'notes' => 'Regular customer - priority delivery'
      ],
      [
        'owner_id' => 1,
        'product_id' => 2,
        'user_id' => 5,
        'order_quantity' => 2,
        'total_amount' => 3000.00,
        'order_status' => 'cancelled',
        'sold_price' => 1500.00,
        'cash_collected' => 0.00,
        'change_returned' => 0.00,
        'empty_collected' => 0,
        'notes' => 'Customer cancelled order'
      ]
    ];

    foreach ($orders as $order) {
      DB::table('orders')->insert($order);
    }
  }
}