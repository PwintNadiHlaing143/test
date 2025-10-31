<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('delivery_routes', function (Blueprint $table) {
      $table->id('route_id');
      $table->unsignedBigInteger('group_id');
      $table->unsignedBigInteger('order_id');
      $table->unsignedBigInteger('supervisor_id');
      $table->unsignedBigInteger('township_id');

      $table->string('delivery_status', 100);
      $table->dateTime('delivery_date')->useCurrent();

      // Foreign key constraints
      $table->foreign('group_id')
        ->references('group_id')
        ->on('delivery_group')
        ->onDelete('cascade')
        ->onUpdate(action: 'cascade');

      $table->foreign('order_id')
        ->references('order_id')
        ->on('orders')
        ->onDelete('cascade')
        ->onUpdate(action: 'cascade');
      $table->foreign('supervisor_id')
        ->references('supervisor_id')
        ->on('supervisors')
        ->onDelete('cascade')
        ->onUpdate(action: 'cascade');
      $table->foreign('township_id')
        ->references('township_id')
        ->on('townships')
        ->onDelete('cascade')
        ->onUpdate(action: 'cascade');
      // Indexes
      $table->index('group_id');
      $table->index('order_id');
      $table->index('supervisor_id');
      $table->index('township_id');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('delivery_route');
  }
};