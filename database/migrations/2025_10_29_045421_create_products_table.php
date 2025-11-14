<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

  public function up(): void
  {
    Schema::create('products', function (Blueprint $table) {
      $table->id('product_id');
      $table->unsignedBigInteger('owner_id');
      $table->string('product_name', 255);
      $table->decimal('unit_price', 10, 2);
      $table->integer('current_stock')->default(0);
      $table->text('description')->nullable();
      $table->string('product_image')->nullable();
      $table->boolean('product_status')->default(true);
      $table->timestamps();

      // Foreign key constraint
      $table->foreign('owner_id')
        ->references('owner_id')
        ->on('owner')
        ->onDelete('cascade');

      // Indexes for better performance
      $table->index('owner_id');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('products');
  }
};
