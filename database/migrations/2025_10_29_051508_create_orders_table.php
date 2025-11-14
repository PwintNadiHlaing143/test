<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

  public function up(): void
  {
    Schema::create('orders', function (Blueprint $table) {
      $table->id('order_id');
      $table->unsignedBigInteger('owner_id');
      $table->unsignedBigInteger('product_id');
      $table->unsignedBigInteger('user_id');
      $table->integer('order_quantity');
      $table->decimal('total_amount', 10, 2);
      $table->dateTime('order_date')->useCurrent();
      $table->string('order_status', 50)->default('pending');
      // $table->decimal('sold_price', 10, 2)->nullable();
      // $table->decimal('cash_collected', 10, 2)->default(0);
      // $table->decimal('change_returned', 10, 2)->default(0);
      $table->unsignedBigInteger('sold_price')->nullable();
      $table->unsignedBigInteger('cash_collected')->default(0);
      $table->unsignedBigInteger('change_returned')->default(0);
      $table->integer('empty_collected')->default(0);
      $table->text('notes')->nullable();
      $table->timestamps();


      // Foreign key constraints
      $table->foreign('owner_id')
        ->references('owner_id')
        ->on('owner')
        ->onDelete('cascade')
        ->onUpdate(action: 'cascade');

      $table->foreign('product_id')
        ->references('product_id')
        ->on('products')
        ->onDelete('cascade')
        ->onUpdate(action: 'cascade');

      // Indexes
      $table->index('owner_id');
      $table->index('product_id');
      $table->index('user_id');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('orders');
  }
};